<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorHTML;
use Picqer\Barcode\BarcodeGeneratorPNG;
use App\Models\Barcode;
use Illuminate\Support\Facades\Storage;

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
        $orderColumn = $request->input('columns' . $orderColumnIndex . 'data', 'id');
        $orderDirection = $request->input('order.0.dir', 'asc');

        $query = Product::query();

        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('product_type', 'like', '%' . $searchValue . '%')
                    ->orWhere('code', 'like', '%' . $searchValue . '%')
                    ->orWhere('barcode_symbology', 'like', '%' . $searchValue . '%')
                    ->orWhere('category', 'like', '%' . $searchValue . '%')
                    ->orWhere('tax_method', 'like', '%' . $searchValue . '%')
                    ->orWhere('quantity', 'like', '%' . $searchValue . '%');
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
            $action .= "<a href='" . $url . "/products/edit/" . $employee->id . "' class='btn btn-info mr_2'>Edit</a>";
            $action .= '<button type="button" onclick="delete_store(' . $employee->id . ')" class="btn btn-danger ml-2">Delete</button>';

            $records[] = [
                'name' => $employee->name,
                'code' => $employee->code,
                'category' => $employee->category,
                'cost' => $employee->cost,
                'price' => $employee->price,
                'is_active' => $employee->is_active,
                'created_at' => date('d-m-Y h:s', strtotime($employee->created_at)),
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
        $barcode = $generator->getBarcode('123456789', $generator::TYPE_CODE_128);
           return view('products.create', compact('barcode'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // dd($_POST);
        $validatedData = $request->validate([
            'product_type' => 'required|string',
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:products',
            'barcode_symbology' => 'required|string',
            'category' => 'required|string',
            'cost' => 'required|numeric',
            'price' => 'required|numeric',
            'tax_method' => 'required|string',
            'quantity' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
        ]);
// print_r($_FILES);
//         dd($_POST);
        // try {

            // Generate barcode
            $generator = new BarcodeGeneratorPNG();
            $barcodeData = $generator->getBarcode($request->code, $generator::TYPE_CODE_128);
    
            $filePath = 'barcodes/' . $request->code . '.png';
            Storage::disk('public')->put($filePath, $barcodeData);
    
            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('product_images', 'public');
                $validatedData['image'] = $imagePath;
            }
// dd($validatedData);  /  
            // Save product in a transaction
            // \DB::transaction(function () use ($validatedData) {
                $dd=Product::create($validatedData);
            // });
    // dd($dd);
            return redirect()->back()->with('success', 'Product added successfully!');
        // } catch (\Exception $e) {
        //     // Log the error for debugging
        //     // \Log::error('Error storing product: ' . $e->getMessage());
    
        //     return redirect()->back()->with('error', 'An error occurred while adding the product. Please try again.');
        // }

        // $generator = new BarcodeGeneratorPNG();
        // $barcodeData = $generator->getBarcode($request->code, $generator::TYPE_CODE_128);

        // $filePath = 'barcodes/' . $request->code . '.png';
        // Storage::put('public/' . $filePath, $barcodeData);
    
        // // Handle image upload
        // if ($request->hasFile('image')) {
        //     $imagePath = $request->file('image')->store('product_images', 'public');
        //     $validatedData['image'] = $imagePath;
        // }
    
        // Product::create($validatedData);
    
        // return redirect()->back()->with('success', 'Product added successfully!');
    
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
