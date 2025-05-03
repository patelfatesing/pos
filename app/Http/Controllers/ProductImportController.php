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

class ProductImportController extends Controller
{
    public function showUploadForm()
    {

        return view('products_import.import');
    }
    
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);
    
        $file = $request->file('file');
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
    
        $header = fgetcsv($handle); // read CSV header
        $inserted = 0;
        $skipped = 0;
    
        while (($row = fgetcsv($handle)) !== false) {

            $subcategories = SubCategory::where('name', $row[6])->first();

            $categories = Category::where('name', $row[5])->first();

            $category_id = null;
            $sub_category_id = null;
            
            if(!empty($subcategories)){
                $sub_category_id = $subcategories->id;
            }

            if(!empty($categories)){
                $category_id = $categories->id;
            }
            // dd($row);

            // Skip duplicate check
            $existing = DB::table('products')
                ->join('inventories', 'products.id', '=', 'inventories.product_id')
                ->where('products.name', $row[0])
                ->where('inventories.batch_no', $row[2])
                ->whereDate('inventories.expiry_date', $row[3])
                ->first();
    
            if ($existing) {
                $skipped++;
                continue;
            }
    
            // Insert product (or find existing by name)
            $product = DB::table('products')->where('name', $row[0])->first();
            $productId = null;
    
            if (!$product) {

                $brand = preg_replace('/\s\d{2,4}ml\b/i', '', $row[0]);
                $sku = Product::generateSku($brand, $row[0], $row[7]);
           
                $validatedData['sku'] = $sku;

                $productId = DB::table('products')->insertGetId([
                    'name' => $row[0],
                    'brand' => $brand,
                    'barcode' => $row[1],
                    'size' => $row[7],
                    'sku' => $sku,
                    'category_id' => $category_id,
                    'subcategory_id' => $sub_category_id,
                    'created_at' => $created_at ?? now(),
                    'cost_price' => $row[8],
                    'sell_price' => $row[13],
                    'discount_price' => $row[15],
                    'discount_amt' => $row[16],
                    'case_size' => $row[11],
                    'box_unit' => $row[17],
                    'secondary_unitx' => $row[18],
                ]);
            } else {
                $productId = $product->id;
            }
    
            // Insert inventory
            DB::table('inventories')->insert([
                'product_id' => $productId,
                'store_id' => 1,
                'location_id' => 1,
                'batch_no' => $row[2],
                'expiry_date' => Carbon::createFromFormat('d-m-Y', $row[4])->format('Y-m-d'),
                'mfg_date' => Carbon::createFromFormat('d-m-Y', $row[3])->format('Y-m-d'),
                'quantity' => $row[5],
            ]);
    
            $inserted++;
        }
    
        fclose($handle);
    
        return back()->with('success', "$inserted records inserted, $skipped duplicates skipped.");
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
}
