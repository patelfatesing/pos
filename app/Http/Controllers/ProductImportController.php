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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductImportController extends Controller
{
    public function showUploadForm()
    {
        return view('products_import.import');
    }

    public function import(Request $request)
    {

        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $csv = array_map('str_getcsv', file($file));
        $headers = array_map('trim', $csv[0]); // CSV headers

        $file = $request->file('file');
        $path = $file->getRealPath();


        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Generate new filename with current date and time
            $filename = now()->format('dmYHis') . '.' . $file->getClientOriginalExtension();

            // Store the file in 'product_import' folder with the new name
            $file->storeAs('product_import', $filename, 'public');
        }

        // Get DB columns from a specific table, for example: products
        $dbFields = Schema::getColumnListing('products');

        $dbFields = [
            1 => "Name",
            2 => "Barcode",
            3 => "Batch No",
            4 => "Mfg Date",
            5 => "Expiry Date",
            6 => "Category",
            7 => "Sub Category",
            8 => "Pack Size",
            9 => "Stock Low Level",
            10 => "Cost Price",
            11 => "Sell Price",
            12 => "MRP",
            13 => "Discount Price",
            14 => "Discount Amt",
            15 => "Case Size",
        ];

        // dd($dbFields);
        return view('products_import.csv-preview', compact('headers', 'dbFields', 'filename'));


        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Generate new filename with current date and time
            $filename = now()->format('dmYHis') . '.' . $file->getClientOriginalExtension();

            // Store the file in 'product_import' folder with the new name
            $file->storeAs('product_import', $filename, 'public');
        }


        $handle = fopen($path, 'r');

        $header = fgetcsv($handle); // read CSV header
        $inserted = 0;
        $skipped = 0;
        $updated = 0;

        while (($row = fgetcsv($handle)) !== false) {

            $categories = Category::where('name', $row[5])->first();
            $subcategories = SubCategory::where('name', $row[6])->first();
            $category_id = $categories?->id;
            $sub_category_id = $subcategories?->id;

            if (!empty($sub_category_id) && !empty($category_id)) {

                // Check for duplicates
                $existing = DB::table('products')
                    ->join('inventories', 'products.id', '=', 'inventories.product_id')
                    ->where('products.name', $row[0])
                    ->where('inventories.batch_no', $row[2])
                    ->whereDate('inventories.expiry_date', Carbon::createFromFormat('d-m-Y', $row[4])->format('Y-m-d'))
                    ->first();

                // if ($existing) {
                //     $skipped++;
                //     continue;
                // }

                // Insert or find product
                $product = DB::table('products')->where('name', $row[0])->first();
                $productId = null;

                if (!$product) {
                    $brand = preg_replace('/\s\d{2,4}ml\b/i', '', $row[0]);
                    $sku = Product::generateSku($brand, $row[2], $row[7]);

                    $productId = DB::table('products')->insertGetId([
                        'name' => $row[0],
                        'brand' => $brand,
                        'barcode' => (string) $row[1],
                        'size' => $row[7],
                        'sku' => $sku,
                        'category_id' => $category_id,
                        'subcategory_id' => $sub_category_id,
                        'created_at' => now(),
                        'cost_price' => $row[8],
                        'sell_price' => $row[13],
                        'reorder_level' => $row[14],
                        'discount_price' => $row[15],
                        'discount_amt' => $row[16],
                        'case_size' => $row[11],
                        'box_unit' => $row[17],
                        'secondary_unitx' => $row[18],
                    ]);

                    $inserted++;
                } else {

                    if ($product->barcode != (string) $row[1] || $product->sell_price != $row[13]) {
                        $product_data = Product::findOrFail($product->id);
                        $product_data->barcode = (string) $row[1];
                        $product_data->cost_price = $row[8];
                        $product_data->sell_price = $row[13];
                        $product_data->discount_price = $row[15];
                        $product_data->discount_amt = $row[16];
                        $product_data->save();

                        $productId = $product_data->id;
                        $updated++;
                    }
                }

                if (!empty($productId)) {

                    // Insert inventory
                    DB::table('inventories')->insert([
                        'product_id' => $productId,
                        'store_id' => 1,
                        'location_id' => 1,
                        'batch_no' => $row[2],
                        'expiry_date' => Carbon::parse($row[4])->format('Y-m-d'),
                        'mfg_date' => Carbon::parse($row[3])->format('Y-m-d'),
                        'quantity' => 0,
                    ]);
                }

                // Only increment if insert actually happens

            }
        }


        fclose($handle);

        return redirect()->route('products.list')->with('success', "$inserted records inserted, $skipped duplicates skipped,$updated updated products.");
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


    public function preview(Request $request)
    {
        $filename = $request->input('file_name'); // e.g., "product_sample.csv"
        $fullPath = storage_path('app/public/product_import/' . $filename);

        $mapping = $request->input('mapping');

        if (!file_exists($fullPath)) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        $csvData = array_map('str_getcsv', file($fullPath));

        $inserted = $updated = $skipped = 0;
        $productId = null;

        // Skip header
        for ($i = 1; $i < count($csvData); $i++) {
            $row = $csvData[$i];

            // Dynamically map values from row using $mapping
            $name = $row[$mapping['Name']] ?? null;
            $barcode = $row[$mapping['Barcode']] ?? null;
            $batch_no = $row[$mapping['Batch No']] ?? null;
            $mfg_date = Carbon::parse($row[$mapping['Mfg Date']])->format('Y-m-d');
            $expiry_date = Carbon::parse($row[$mapping['Expiry Date']])->format('Y-m-d');
            $category_id = $row[$mapping['Category']] ?? null;
            $sub_category_id = $row[$mapping['Sub Category']] ?? null;

            $categories = Category::where('name', $category_id)->first();
            $subcategories = SubCategory::where('name', $sub_category_id)->first();
            $category_id = $categories?->id;
            $sub_category_id = $subcategories?->id;


            if (empty($category_id) || empty($sub_category_id)) {
                $skipped++;
                continue;
            }

            // Check for existing inventory record
            $existing = DB::table('products')
                ->join('inventories', 'products.id', '=', 'inventories.product_id')
                ->where('products.name', $name)
                ->where('inventories.batch_no', $batch_no)
                ->whereDate('inventories.expiry_date', $expiry_date)
                ->first();

            if ($existing) {
                $skipped++;
                continue;
            }

            // Find or Insert Product
            $product = DB::table('products')->where('name', $name)->first();


            if (!$product) {
                $brand = preg_replace('/\s\d{2,4}ml\b/i', '', $name);
                $sku = Product::generateSku($brand, $batch_no, $row[$mapping['Pack Size']]);

                $productId = DB::table('products')->insertGetId([
                    'name' => $name,
                    'brand' => $brand,
                    'barcode' => (string) $barcode,
                    'size' => $row[$mapping['Pack Size']] ?? null,
                    'sku' => $sku,
                    'category_id' => $category_id,
                    'subcategory_id' => $sub_category_id,
                    'cost_price' => $row[$mapping['Cost Price']] ?? null,
                    'sell_price' => $row[$mapping['Sell Price']] ?? null,
                    'mrp' => $row[$mapping['MRP']] ?? null,
                    'reorder_level' => $row[$mapping['Stock Low Level']] ?? null,
                    'discount_price' => $row[$mapping['Discount Price']] ?? null,
                    'discount_amt' => $row[$mapping['Discount Amt']] ?? null,
                    'case_size' => $row[$mapping['Case Size']] ?? null,
                ]);

                DB::table('inventories')->insert([
                    'product_id' => $productId,
                    'store_id' => 1,
                    'location_id' => 1,
                    'batch_no' => $batch_no,
                    'expiry_date' => $expiry_date,
                    'mfg_date' => $mfg_date,
                    'quantity' => 0,
                ]);

                $inserted++;
            } else {
                if (
                    $product->barcode != (string) $barcode ||
                    $product->sell_price != ($row[$mapping['Sell Price']] ?? null)
                ) {
                    $productModel = Product::find($product->id);
                    $productModel->barcode = (string) $barcode;
                    $productModel->cost_price = $row[$mapping['Cost Price']] ?? null;
                    $productModel->sell_price = $row[$mapping['Sell Price']] ?? null;
                    $productModel->discount_price = $row[$mapping['Discount Price']] ?? null;
                    $productModel->discount_amt = $row[$mapping['Discount Amt']] ?? null;
                    $productModel->save();

                    $productId = $productModel->id;
                    $updated++;
                } else {
                    $productId = $product->id;
                }
            }
        }

        // return response()->json([
        //     'inserted' => $inserted,
        //     'updated' => $updated,
        //     'skipped' => $skipped,
        // ]);

        return redirect()->route('products.list')->with('success', "$inserted records inserted, $skipped duplicates skipped,$updated updated products.");
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
                'case_size' => $row['case_size'],
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
        dd("sdfsdf");
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
            ->orderBy('id', 'asc')
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
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $from_store_id = $request->from_store_id;

        $inventoryService = new \App\Services\InventoryService();


        foreach ($request->items as $product_id => $product) {

            $record = Product::with('inventorie')->where('id', $product_id)->where('is_deleted', 'no')->firstOrFail();

            $inventory = Inventory::findOrFail($record->inventorie->id);

            if ($from_store_id == 1) {

                if (!empty($inventory['quantity'])) {
                    $qnt =  $inventory['quantity'] + $product['quantity'];
                } else {
                    $qnt =  $product['quantity'];
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
                        $qnt =  $product['quantity'];
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
                            'quantity' => $product['quantity'],
                            'added_by' => Auth::id()
                        ]
                    );
                }
            }

            $date = Carbon::today();

            DailyProductStock::updateOrCreate(
                [
                    'product_id' => $product_id,
                    'branch_id' => $from_store_id,
                    'date' => $date,
                    'opening_stock' => $product['quantity'],
                ]
            );

            $inventoryService->transferProduct($product_id, $inventory->id, $from_store_id, '', $product['quantity'], 'add_stock');
        }

        return redirect()->route('inventories.list')->with('success', 'Opening Stock has beeb added successfully.');
    }
}
