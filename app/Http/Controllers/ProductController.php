<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorHTML;
use Picqer\Barcode\BarcodeGeneratorPNG;
use App\Models\Barcode;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\PackSize;

class ProductController extends Controller
{
    public function index()
    {
        // if (!auth()->user()->hasPermission('View')) {
        //     abort(403, 'Unauthorized - You do not have the required permission.');
        // }

        $data = Product::where('is_deleted', 'no')->get();
        return view('products.index', compact('data'));
    }

    public function getData(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $request->input("columns.$orderColumnIndex.data", 'id');
        $orderDirection = $request->input('order.0.dir', 'asc');
    
        $query = Product::with('category');
    
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('brand', 'like', '%' . $searchValue . '%')
                    ->orWhere('sku', 'like', '%' . $searchValue . '%')
                    ->orWhere('abv', 'like', '%' . $searchValue . '%')
                    ->orWhere('size', 'like', '%' . $searchValue . '%')
                    ->orWhereHas('category', function ($q2) use ($searchValue) {
                        $q2->where('name', 'like', '%' . $searchValue . '%');
                    });
            });
        }
    
        $recordsTotal = Product::count();
        $recordsFiltered = $query->count();
    
        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();
    
        $records = [];
        $url = url('/');
    
        foreach ($data as $employee) {
            $action = "";
            $action .= "<a href='" . $url . "/inventories/add-stock/" . $employee->id . "' class='btn btn-info mr-2'>Add Stock</a>";
            $action .= "<a href='" . $url . "/products/edit/" . $employee->id . "' class='btn btn-info mr-2'>Edit</a>";
            $action .= "<a href='" . $url . "/products/barcode-print/" . $employee->id . "' class='btn btn-info mr-2'>Print</a>";
           
            $action .= '<button type="button" onclick="delete_product(' . $employee->id . ')" class="btn btn-danger ml-2">Delete</button>';
    
            $records[] = [
                'name' => $employee->name,
                'brand' => $employee->brand,
                'category' => $employee->category->name ?? 'N/A',
                'sku' => $employee->sku,
                'size' => $employee->size,
                'is_active' => $employee->is_active,
                'created_at' => date('d-m-Y h:i', strtotime($employee->created_at)),
                'action' => $action
            ];
        }
    
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records
        ]);
    }
    
        /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $generator = new BarcodeGeneratorHTML();
        $categories = Category::all();

        $productCode = '123456789';

        // $barcode = $generator->getBarcode('123456789', $generator::TYPE_CODE_128);
           return view('products.create', compact('categories','productCode'));
    }

    public function generateBarcode($productCode)
    {
        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($productCode, $generator::TYPE_CODE_128);

        return response($barcode)
            ->header('Content-Type', 'image/png');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string',
            'category_id' => 'required|numeric',
            'subcategory_id' => 'required|numeric',
            'size' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
        ]);
        
        // try {

        $pack_size = $request->size;
            $sku = Product::generateSku($request->brand, $request->name, $request->size);
           
            $validatedData['sku'] = $sku;
            $code = str_replace('-', '', $sku);
            // Generate barcode
            $generator = new BarcodeGeneratorPNG();
            $barcodeData = $generator->getBarcode($code, $generator::TYPE_CODE_128);
    
            $filePath = 'barcodes/' . $code . '.png';
            Storage::disk('public')->put($filePath, $barcodeData);

            $validatedData['barcode'] = $code;
    
            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('product_images', 'public');
                $validatedData['image'] = $imagePath;
            }
            // Save product in a transaction
            // \DB::tppransaction(function () use ($validatedData) {
                $validatedData['size'] = $pack_size;
                // dd($validatedData);
                Product::create($validatedData);
            // });
    
            return redirect()->route('products.list')->with('success', 'Item created successfully.');
   
            // return redirect()->back()->with('success', 'Product added successfully!');
        // } catch (\Exception $e) {
        //     // Log the error for debugging
        //     \Log::error('Error storing product: ' . $e->getMessage());
    
        //     return redirect()->back()->with('error', 'An error occurred while adding the product. Please try again.');
        // }

    
    }


    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $record = Product::where('id', $id)->where('is_deleted', 'no')->firstOrFail();
        return view('products.edit', compact('record'));
    }

    public function getSubcategories($category_id)
    {
        $subcategories = SubCategory::where('category_id', $category_id)->get();
        return response()->json($subcategories);
    }

    public function getPackSize($sub_category_id)
    {
        $packSize = PackSize::where('sub_category_id', $sub_category_id)->get();
        return response()->json($packSize);
    }

    public function barcodePrint($id)
    {
        $product_details = Product::where('id', $id)->where('is_deleted', 'no')->firstOrFail();
    //    dd($record);
        return view('products.barcode_print', compact('product_details'));
    }

    public function check()
    {
        // $record = Product::where('id', $id)->where('is_deleted', 'no')->firstOrFail();
        return view('products.check');
    }

    public function pic()
    {
        // $record = Product::where('id', $id)->where('is_deleted', 'no')->firstOrFail();
        return view('products.pic_capture');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function barcodeCheck(Request $request)
    {
        $barcode = Product::where('code', $request->code)->first();

        dd($barcode);
        $record = Product::where('id', $id)->where('is_deleted', 'no')->firstOrFail();
        return view('products.edit', compact('record'));
    }

    public function uploadPhotp(Request $request)
    {

        $filename = '';
        return response()->json(['success' => true, 'filename' => $filename]);
        $barcode = Product::where('code', $request->code)->first();

        dd($barcode);
        $record = Product::where('id', $id)->where('is_deleted', 'no')->firstOrFail();
        return view('products.edit', compact('record'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}