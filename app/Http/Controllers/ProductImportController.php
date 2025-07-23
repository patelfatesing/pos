<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Collection;
use App\Models\Product; // your model
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\DailyProductStock;
use App\Models\ShiftClosing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductImportController extends Controller
{
    public function showUploadForm()
    {
        return view('products_import.import');
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:10240', // max 10MB
            ]);

            if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
                return redirect()->route('products.import')->withErrors(['file' => 'The uploaded file is invalid.']);
            }

            $file = $request->file('file');

            // Read and validate CSV structure
            $csv = array_map('str_getcsv', file($file));
            if (empty($csv) || count($csv) < 2) { // At least header row and one data row
                return redirect()->route('products.import')->withErrors(['file' => 'The CSV file must contain a header row and at least one data row.']);
            }

            $headers = array_map('trim', $csv[0]); // CSV headers
            if (empty($headers)) {
                return redirect()->route('products.import')->withErrors(['file' => 'The CSV file must contain headers.']);
            }

            // Generate new filename with current date and time
            $filename = now()->format('dmYHis') . '.' . $file->getClientOriginalExtension();

            // Store the file in 'product_import' folder with the new name
            $file->storeAs('product_import', $filename, 'public');

            // Redirect to mapping form
            return redirect()->route('products.mapping', ['filename' => $filename]);
        } catch (\Exception $e) {
            Log::error('Product import error: ' . $e->getMessage());
            return redirect()->route('products.import')->withErrors(['system_error' => 'An error occurred while processing the file. Please try again.']);
        }
    }

    public function showMappingForm($filename)
    {
        $fullPath = storage_path('app/public/product_import/' . $filename);

        if (!file_exists($fullPath)) {
            return redirect()->route('products.import')
                ->withErrors(['file' => 'The uploaded file is no longer available. Please try uploading again.']);
        }

        try {
            $csv = array_map('str_getcsv', file($fullPath));
            $headers = array_map('trim', $csv[0]); // CSV headers

            // Define database fields with descriptions
            $dbFields = [
                'Name' => 'product_name',
                'Brand' => 'brand',
                'Barcode' => 'barcode',
                'Batch No' => 'batch_no',
                'Mfg Date' => 'mfg_date',
                'Expiry Date' => 'exp_date',
                'Category' => 'category',
                'Sub Category' => 'sub_category',
                'Pack Size' => 'pack_size',
                'Stock Low Level' => 'min_stock_qty_set',
                'Cost Price' => 'cost_price',
                'Sale Price' => 'sale_price',
                'MRP' => 'mrp',
                'Discount Price' => 'commssion_base_customer_sale_price',
                'Discount Amt' => 'commssion_margin',
            ];

            return view('products_import.csv-preview', compact('headers', 'dbFields', 'filename'));
        } catch (\Exception $e) {
            Log::error('Error showing mapping form: ' . $e->getMessage());
            return redirect()->route('products.import')
                ->withErrors(['system_error' => 'An error occurred while processing the file. Please try uploading again.']);
        }
    }

    public function processImport(Request $request)
    {

        try {
            $request->validate([
                'file_name' => 'required|string',
                'mapping' => 'required|array',
                'mapping.product_name' => 'required',
                'mapping.barcode' => 'required',
                'mapping.batch_no' => 'required',
                'mapping.category' => 'required',
                'mapping.sub_category' => 'required',
                'mapping.cost_price' => 'required',
                'mapping.sale_price' => 'required',
                'mapping.min_stock_qty_set' => 'required',
                'mapping.brand' => 'required',
            ], [
                'mapping.required' => 'Please map all required fields.',
                'mapping.product_name.required' => 'Product Name field mapping is required.',
                'mapping.category.required' => 'Category field mapping is required.',
                'mapping.sub_category.required' => 'Sub Category field mapping is required.',
                'mapping.cost_price.required' => 'Cost Price field mapping is required.',
                'mapping.sale_price.required' => 'Sale Price field mapping is required.',
                'mapping.min_stock_qty_set.required' => 'Stock Low Level field mapping is required.',
                'mapping.brand.required' => 'Stock Low Level field mapping is required.',
            ]);

            $filename = $request->input('file_name');
            $fullPath = storage_path('app/public/product_import/' . $filename);

            if (!file_exists($fullPath)) {
                return back()
                    ->withErrors(['file' => 'The uploaded file is no longer available. Please try uploading again.']);
            }

            $rows = file($fullPath);
            $csvData = [];

            foreach ($rows as $row) {
                $fields = str_getcsv($row);
                $formattedFields = array_map(function ($value) {
                    if (preg_match('/^\d+\.?\d*E\+?\d+$/i', $value)) {
                        $value = number_format((float)$value, 0, '', '');
                    }
                    return $value;
                }, $fields);

                $csvData[] = $formattedFields;
            }

            $mapping = $request->input('mapping');
            $inserted = $updated = $skipped = $cate_sub_not_match = 0;

            DB::beginTransaction();

            try {
                // Skip header
                for ($i = 1; $i < count($csvData); $i++) {
                    $row = $csvData[$i];
                    // Map values from row using $mapping
                    $name = $row[$mapping['product_name']] ?? null;
                    $barcode = $row[$mapping['barcode']] ?? null;
                    $batch_no = $row[$mapping['batch_no']] ?? null;
                    $mfg_date = $row[$mapping['mfg_date']];
                    $expiry_date = $row[$mapping['exp_date']];
                    $category_name = $row[$mapping['category']] ?? null;
                    $sub_category_name = $row[$mapping['sub_category']] ?? null;
                    $pack_size = $row[$mapping['pack_size']] ?? null;
                    $sale_price = $row[$mapping['sale_price']] ?? null;
                    $brand = $row[$mapping['brand']] ?? null;

                    // Validate required fields
                    if (!$name || !$category_name || !$sub_category_name || !$sale_price) {
                        $skipped++;
                        continue;
                    }

                    // Find category and subcategory
                    $category = Category::where('name', $category_name)->first();
                    $subcategory = SubCategory::where('name', $sub_category_name)->first();

                    if (!$category || !$subcategory) {
                        $cate_sub_not_match++;
                        continue;
                    }

                    // Check for existing product
                    $existing = Product::where('name', $name)
                        ->where('is_deleted', 'no')
                        ->first();

                    if ($existing) {
                        // Update existing product
                        // $existing->barcode = $barcode;
                        $existing->reorder_level = isset($mapping['min_stock_qty_set']) ? $row[$mapping['min_stock_qty_set']] : 0;
                        $existing->mrp = isset($mapping['mrp']) ? $row[$mapping['mrp']] : 0;
                        $existing->cost_price = $row[$mapping['cost_price']] ?? null;
                        $existing->sell_price = $sale_price;
                        $existing->discount_price = isset($mapping['commssion_base_customer_sale_price']) ? $row[$mapping['commssion_base_customer_sale_price']] : null;
                        $existing->discount_amt = isset($mapping['commssion_margin']) ? $row[$mapping['commssion_margin']] : 0;
                        $existing->save();

                        $updated++;
                    } else {
                        // Create new product
                        $brand = preg_replace('/\s\d{2,4}ml\b/i', '', $name);
                        $product_l_id = Product::max('id') ?? 0;

                        $sku = Product::generateSku($brand, $batch_no, $pack_size, $product_l_id + 1);

                        $product = Product::create([
                            'name' => $name,
                            'brand' => $row[$mapping['brand']] ?? null,
                            'barcode' => $barcode,
                            'size' => $pack_size,
                            'sku' => $sku,
                            'category_id' => $category->id,
                            'subcategory_id' => $subcategory->id,
                            'cost_price' => $row[$mapping['cost_price']] ?? null,
                            'sell_price' => $sale_price,
                            'mrp' => isset($mapping['mrp']) ? $row[$mapping['mrp']] : 0,
                            'reorder_level' => isset($mapping['min_stock_qty_set']) ? $row[$mapping['min_stock_qty_set']] : null,
                            'discount_price' => isset($mapping['commssion_base_customer_sale_price']) ? $row[$mapping['commssion_base_customer_sale_price']] : null,
                            'discount_amt' => isset($mapping['commssion_margin']) ? $row[$mapping['commssion_margin']] : 0,
                        ]);

                        // Create inventory record
                        // if ($mfg_date && $expiry_date) {
                        Inventory::create([
                            'product_id' => $product->id,
                            'store_id' => 1,
                            'location_id' => 1,
                            'batch_no' => $batch_no,
                            'expiry_date' => $expiry_date,
                            'mfg_date' => $mfg_date,
                            'quantity' => 0,
                            'low_level_qty' => isset($mapping['min_stock_qty_set']) ? $row[$mapping['min_stock_qty_set']] : null,
                        ]);
                        // }

                        $inserted++;
                    }
                }

                DB::commit();

                // Clean up the uploaded file
                @unlink($fullPath);

                return redirect()->route('products.list')
                    ->with('success', "$inserted records inserted, $cate_sub_not_match category/sub-category not matched, " .
                        "$skipped skipped, $updated updated products.");
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Product import error: ' . $e->getMessage());
                return back()
                    ->withErrors(['system_error' => 'An error occurred while importing products: ' . $e->getMessage()])
                    ->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Product import mapping error: ' . $e->getMessage());
            return back()
                ->withErrors(['system_error' => 'An error occurred while processing the file: ' . $e->getMessage()])
                ->withInput();
        }
    }

    // Try to parse using multiple known formats
    function normalizeDate($dateString)
    {
        $formats = ['d/m/Y', 'd-m-Y', 'm/d/Y', 'Y-m-d'];
        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $dateString);
                if ($date && $date->format($format) === $dateString) {
                    return $date->format('Y-m-d'); // DB-compatible format
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Fallback: return null or current date
        return null; // or return now()->format('Y-m-d');
    }

    public function importData(Request $request)
    {
        $request->validate([
            'mapping' => 'required|array'
        ]);

        $path = Session::get('import_file');
        $data = Session::get('import_data');

        if (!$data) {
            return redirect()->route('products.import')->with('error', 'No data found. Please upload again.');
        }

        foreach ($data as $row) {
            Product::create([
                'name' => $row[$request->mapping['name']] ?? null,
                'sku' => $row[$request->mapping['sku']] ?? null,
                'price' => $row[$request->mapping['price']] ?? 0,
                'stock' => $row[$request->mapping['stock']] ?? 0,
            ]);
        }

        Session::forget('import_file');
        Session::forget('import_data');

        return redirect()->route('products.import')->with('success', 'Products imported successfully.');
    }

    public function addStocks()
    {
        $products = Product::with('inventories')
            ->orderBy('id', 'asc')->where('is_deleted', 'no')
            ->get();
        $stores = Branch::where('is_active', 'yes')->where('is_deleted', 'no')->latest()->get();

        return view('products_import.add_stocks', compact('products', 'stores'));
    }

    public function importStocks(Request $request)
    {
        $request->validate([
            'from_store_id' => 'required|exists:branches,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            // 'items.*.quantity' => 'required|integer|min:1',
        ]);

        $from_store_id = $request->from_store_id;

        $inventoryService = new \App\Services\InventoryService();

        foreach ($request->items as $product_id => $product) {

            $record = Product::with('inventorieUnfiltered')->where('id', $product_id)->where('is_deleted', 'no')->firstOrFail();
            $inventory = Inventory::findOrFail($record->inventorieUnfiltered->id);

            if ($from_store_id == 1) {

                if (!empty($inventory['quantity'])) {
                    $qnt =  $inventory['quantity'] + $product['quantity'];
                } else {
                    $qnt =  !empty($product['quantity']) ? $product['quantity'] : 0;
                }

                $inventory->updated_at = now();
                $inventory->quantity = $qnt;

                $inventory->save();
            } else {

                $inventory_branch = Inventory::where('product_id', $product_id)->where('store_id', $from_store_id)->first();

                if (!empty($inventory_branch)) {

                    if (!empty($inventory_branch['quantity'])) {
                        $qnt =  $inventory_branch['qnquantityt'] + $product['quantity'];
                    } else {
                        $qnt =  !empty($product['quantity']) ? $product['quantity'] : 0;
                    }

                    $inventory_branch->updated_at = now();
                    $inventory_branch->quantity = $qnt;
                    $inventory_branch->save();
                } else {

                    Inventory::updateOrCreate(
                        [
                            'product_id' => $product_id,
                            'store_id' => $from_store_id,
                            'batch_no' => $inventory->batch_no,
                            'location_id' => $from_store_id,
                            'expiry_date' => $inventory->expiry_date,
                            'mfg_date' => $inventory->mfg_date,
                            'quantity' => !empty($product['quantity']) ? $product['quantity'] : 0,
                            // 'low_level_qty' => $product['reorder_level'],
                            'added_by' => Auth::id()
                        ]
                    );
                }
            }

            $date = Carbon::today();

            $quantity = !empty($product['quantity']) ? $product['quantity'] : 0;

            $running_shift = ShiftClosing::where('branch_id', $from_store_id)
                ->orderBy('id', 'desc')
                ->first();

                

            if (!empty($running_shift)) {
                $shift_id = $running_shift->id ?? null;
                $shift_status = $running_shift->status;

                // Check if stock exists for current shift
                $stock = DailyProductStock::where([
                    'product_id' => $product_id,
                    'branch_id' => $from_store_id,
                    'shift_id' => $shift_id,
                ])->first();
                
                if ($stock) {
                    $stock->added_stock += $quantity;
                    $stock->closing_stock += $quantity;

                    if ($shift_status === 'completed') {
                        $stock->physical_stock += $quantity;
                    }

                    $stock->save();
                } else {
                    // Create new shift-based record
                    DailyProductStock::create([
                        'product_id' => $product_id,
                        'branch_id' => $from_store_id,
                        'shift_id' => $shift_id,
                        'date' => $date,
                        'opening_stock' => 0,
                        'added_stock' => $quantity,
                        'transferred_stock' => 0,
                        'sold_stock' => 0,
                        'closing_stock' => $quantity,
                        'physical_stock' => $quantity,
                        'difference_in_stock' => 0,
                    ]);
                }

            } else {
                // No shift → check if product+branch record exists (ignore date)
                $stock = DailyProductStock::where([
                    'product_id' => $product_id,
                    'branch_id' => $from_store_id,
                    'shift_id' => null,
                ])->first();

                if ($stock) {
                    // Update existing no-shift stock
                    $stock->opening_stock += $quantity;
                    $stock->closing_stock += $quantity;
                    $stock->save();
                } else {
                    // Create new no-shift stock
                    DailyProductStock::create([
                        'product_id' => $product_id,
                        'branch_id' => $from_store_id,
                        'shift_id' => null,
                        'date' => $date,
                        'opening_stock' => $quantity,
                        'added_stock' => 0,
                        'transferred_stock' => 0,
                        'sold_stock' => 0,
                        'closing_stock' => $quantity,
                        'physical_stock' => 0,
                        'difference_in_stock' => 0,
                    ]);
                }
            }

            $inventoryService->transferProduct($product_id, $inventory->id, $from_store_id, '', !empty($product['quantity']) ? $product['quantity'] : 0, 'add_stock');
        }

        return redirect()->route('inventories.list')->with('success', 'Opening Stock has beeb added successfully.');
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $category = Category::where('name', $row['category'])->first();
            $subcategory = SubCategory::where('name', $row['subcategory'])->first();

            $existing = DB::table('products')
                ->join('inventories', 'products.id', '=', 'inventories.product_id')
                ->where('products.name', $row['name'])
                ->where('inventories.batch_no', $row['batch_no'])
                ->whereDate('inventories.expiry_date', Carbon::parse($row['expiry_date']))
                ->first();

            if ($existing) continue;

            $brand = preg_replace('/\s\d{2,4}ml\b/i', '', $row['name']);
            $cleanName = preg_replace('/\d+ml/i', '', $brand);

            $sku = Product::generateSku($cleanName, $row['name'], $row['size']);

            $product = Product::create([
                'name' => $row['name'],
                'brand' => $cleanName,
                'barcode' => $row['barcode'],
                'size' => $row['size'],
                'sku' => $sku,
                'category_id' => $category?->id,
                'subcategory_id' => $subcategory?->id,
                'cost_price' => $row['cost_price'],
                'sell_price' => $row['sell_price'],
                'discount_price' => $row['discount_price'],
                'discount_amt' => $row['discount_amt'],
                'box_unit' => $row['box_unit'],
                'secondary_unitx' => $row['secondary_unitx'],
                'created_at' => now(),
            ]);

            DB::table('inventories')->insert([
                'product_id' => $product->id,
                'store_id' => 1,
                'location_id' => 1,
                'batch_no' => $row['batch_no'],
                'mfg_date' => Carbon::parse($row['mfg_date']),
                'expiry_date' => Carbon::parse($row['expiry_date']),
                'quantity' => $row['quantity'],
            ]);
        }
    }

    public function uploadFile_old(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        $path = $request->file('file')->store('temp');

        $data = Excel::toCollection(null, storage_path('app/' . $path));

        $headings = $data[0][0]->keys()->toArray();

        Session::put('import_file', $path);
        Session::put('import_data', $data[0]);

        return view('products.mapping', compact('headings'));
    }

    public function uploadFile11(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $path = $request->file('file')->getRealPath(); // get temp path

            // Optional: Store the file permanently
            $storedPath = $request->file('file')->store('uploads'); // stored in storage/app/uploads
            // OR $request->file('upload')->storeAs('uploads', 'filename.csv');

            // Read CSV headers
            $file = fopen($path, 'r');
            $header = fgetcsv($file); // read the first line as headers

            // Process the CSV rows if needed
            while (($row = fgetcsv($file)) !== false) {

                dd($row);
                // Handle each row
            }

            fclose($file);

            return back()->with('success', 'CSV file imported successfully!');
        }

        return back()->with('error', 'File upload failed!');
    }
}
