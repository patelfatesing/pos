<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorHTML;
use Picqer\Barcode\BarcodeGeneratorPNG;
use App\Models\Barcode;
use App\Models\Branch;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\PackSize;
use App\Models\CommissionUserImage;
use Illuminate\Support\Facades\Auth;
use App\Models\PartyUserImage;
use Illuminate\Support\Facades\Validator;
use App\Models\ProductPriceChangeHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;



class ProductController extends Controller
{
    public function index()
    {
        // if (!auth()->user()->hasPermission('View')) {
        //     abort(403, 'Unauthorized - You do not have the required permission.');
        // }

        $data = Product::where('is_deleted', 'no')->get();
        $subcategories = SubCategory::where('is_deleted', 'no')->get();

        return view('products.index', compact('data', 'subcategories'));
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

        $query = Product::with(['category', 'subcategory']);

        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('brand', 'like', '%' . $searchValue . '%')
                    ->orWhere('sku', 'like', '%' . $searchValue . '%')
                    ->orWhere('abv', 'like', '%' . $searchValue . '%')
                    ->orWhere('size', 'like', '%' . $searchValue . '%')
                    ->orWhere('barcode', 'like', '%' . $searchValue . '%')
                    ->orWhereHas('category', function ($q2) use ($searchValue) {
                        $q2->where('name', 'like', '%' . $searchValue . '%');
                    });
            });
        }

        $query->where('is_deleted', 'no');

        if ($request->has('sub_category_id') && $request->sub_category_id != '') {
            $query->where('subcategory_id', $request->sub_category_id);
        }

        $recordsTotal = Product::where('is_deleted', 'no')->count();
        $recordsFiltered = $query->count();

        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        $data = $query->orderBy($orderColumn, $orderDirection)->get();
        // $data = $query->orderBy($orderColumn, $orderDirection)
        //     ->offset($start)
        //     ->limit($length)
        //     ->get();

        $records = [];
        $url = url('/');
        if (session('role_name') == "admin") {
            // $url = url('/admin');
        } elseif (session('role_name') == 'wwner') {
            // $url = url('/manager');
        } elseif (session('role_name') == 'warehouse') {
            // $url = url('/employee');
        } else {
            $url = url('');
        }

        foreach ($data as $product) {
            $action = '<div class="d-flex align-items-center list-action">
            <a class="badge badge-primary mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"
                    href="#" onclick="product_price_change(' . $product->id . ',' . $product->sell_price . ')"><i class="ri-currency-line"></i></a>
                     <a class="badge badge-info mr-2" onclick="viewProduct(' . $product->id . ')">
                    <i class="las la-eye"></i>
                </a>
            <a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"
                    href="' . url('/products/edit/' . $product->id) . '"><i class="ri-pencil-line mr-0"></i></a>
   
                                    <a class="badge bg-warning mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"
                                        href="#" onclick="delete_product(' . $product->id . ')"><i class="ri-delete-bin-line mr-0"></i></a>
            </div>';

            $status = ($product->is_active ? '<div class="badge badge-success">Active</div>' : '<div class="badge badge-success">Inactive</div>');
            $records[] = [
                'name' => $product->name,
                'category' => $product->category->name ?? 'N/A',
                'sub_category' => $product->subcategory->name ?? 'N/A',
                'size' => $product->size,
                'brand' => $product->brand,
                'sell_price' => '₹' . rtrim(rtrim($product->sell_price, '0'), '.'),
                'cost_price' => '₹' . rtrim(rtrim($product->cost_price, '0'), '.'),
                'mrp'        => '₹' . rtrim(rtrim($product->mrp, '0'), '.'),
                // 'is_active' => $status,
                'is_active' => $product->is_active == 'yes'
                    ? '<span onclick=\'statusChange("' . $product->id . '", "no")\'><div class="badge badge-success" style="cursor:pointer">Active</div></span>'
                    : '<span onclick=\'statusChange("' . $product->id . '", "yes")\'><div class="badge badge-danger" style="cursor:pointer">Inactive</div></span>',

                'created_at' => date('d-m-Y h:i', strtotime($product->created_at)),
                'updated_at' => date('d-m-Y h:i', strtotime($product->updated_at)),
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
        $packSize = PackSize::all();
        $productCode = '123456789';

        // $barcode = $generator->getBarcode('123456789', $generator::TYPE_CODE_128);
        return view('products.create', compact('categories', 'productCode', 'packSize'));
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

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            // 'brand' => 'required|string',
            'category_id' => 'required|numeric',
            'subcategory_id' => 'required|numeric',
            'size' => 'required|string',
            'cost_price' => 'required|numeric',
            'sell_price' => 'required|numeric',
            'reorder_level' => 'required|integer|min:0',
            'discount_price' => 'nullable|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (
                $request->filled('discount_price') &&
                $request->sell_price <= $request->discount_price
            ) {
                $validator->errors()->add('discount_price', 'Discount price must be less than sell price.');
            }
        });

        $validatedData = $validator->validate();


        // try {

        $pack_size = $request->size;
        $product_l_id = DB::table('products')->select('id')->orderBy('id', 'desc')->first();
        if (empty($product_l_id)) {
            $product_l_id = 0;
        } else {
            $product_l_id = $product_l_id->id;
        }
        if ($request->has('size')) {

            $sku = Product::generateSku($request->brand, $request->batch_no, $request->size, $product_l_id);
        } else {
            $batchNumber = strtoupper($request->sku) . '-' . now()->format('Ymd') . '-' . Str::upper(Str::random(4));
            $sku = Product::generateSku($request->brand, $batchNumber, $request->size, $product_l_id);
        }

        $validatedData['sku'] = $sku;
        $validatedData['mrp'] = $request->mrp;

        // //barcode code
        // $code = str_replace('-', '', $sku);
        // $generator = new BarcodeGeneratorPNG();
        // $barcodeData = $generator->getBarcode($code, $generator::TYPE_CODE_128);
        // $filePath = 'barcodes/' . $code . '.png';
        // Storage::disk('public')->put($filePath, $barcodeData);
        // $validatedData['barcode'] = $code;
        // //barcode code end

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('product_images', 'public');
            $validatedData['image'] = $imagePath;
        }
        // Save product in a transaction
        // \DB::tppransaction(function () use ($validatedData) {
        $validatedData['size'] = $pack_size;
        // dd($validatedData);
        $product =  Product::create($validatedData);

        DB::table('inventories')->insert([
            'product_id' => $product->id,
            'store_id' => 1,
            'location_id' => 1,
            'quantity' => 0
        ]);
        // });

        return redirect()->route('products.list')->with('success', 'Product has been added successfully!');

        // return redirect()->back()->with('success', 'Product added successfully!');
        // } catch (\Exception $e) {
        //     // Log the error for debugging
        //     \Log::error('Error storing product: ' . $e->getMessage());

        //     return redirect()->back()->with('error', 'An error occurred while adding the product. Please try again.');
        // }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $categories = Category::all();
        // $generator = new BarcodeGeneratorHTML();
        // $productCode = '123456789';
        $subcategories = SubCategory::all(); // Fetch subcategories
        $packSizes = PackSize::all(); // Example pack sizes

        $record = Product::where('id', $id)->where('is_deleted', 'no')->firstOrFail();

        return view('products.edit', compact('subcategories', 'packSizes', 'record', 'categories'));
    }

    public function updatePrice(Request $request)
    {
        // 🔍 Validate input
        $validator = Validator::make($request->all(), [
            'old_price' => 'required|numeric|min:0',
            'new_price' => 'required|numeric|min:0',
            'changed_at' => 'required'
        ]);

        $id = $request->product_id;

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // 🧱 Find the product
        $product = Product::findOrFail($id);

        // 💡 Only log if price actually changes
        if ($product->sell_price != $request->new_price) {
            // 💾 Save price change history
            $his_data = ProductPriceChangeHistory::create([
                'product_id' => $product->id,
                'old_price' => $product->sell_price,
                'new_price' => $request->new_price,
                'changed_at' => $request->changed_at,
            ]);
        }

        // 🔁 Update product
        // $product->price = $request->price;
        // $product->save();

        $stores = Branch::where('is_deleted', 'no')->get();

        foreach ($stores as $store) {
            // $arr['id'] = $his_data->id;
            // json_encode(['id' => (string) $his_data->id]);

            sendNotification('price_change', $product->name . ' Product price is changed.', $store->id, Auth::id(), json_encode(['id' => (string)$his_data->id]), 0);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Product price updated successfully.',
            'data' => $product,
        ]);
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

    public function getProducts($sub_category_id)
    {
        $getProductData = Product::where('subcategory_id', $sub_category_id)->where('is_deleted', 'no')->get();
        return response()->json($getProductData);
    }

    public function barcodePrint($id)
    {
        $product_details = Product::where('id', $id)->where('is_deleted', 'no')->firstOrFail();
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

        $record = Product::where('id', $id)->where('is_deleted', 'no')->firstOrFail();
        return view('products.edit', compact('record'));
    }

    public function uploadPhoto(Request $request)
    {
        $isCashier = strtolower(Auth::user()->role->name) == 'cashier';

        $request->validate([
            $isCashier ? 'selectedCommissionUser' : 'selectedPartyUser' => 'required',
        ]);

        $image = $request->file('photo');
        $type = $request->type;
        // Define the custom file name (optional: make it unique using timestamp or UUID)
        $extension = $image->getClientOriginalExtension();
        $userId = $isCashier ? $request->selectedCommissionUser : $request->selectedPartyUser;
        $customFileName = $type . '_' . $userId . '_image.' . $extension;

        $path = $image->storeAs('uploaded_photos', $customFileName, 'public');

        // Unified session key
        $sessionKey = $isCashier ? 'checkout_images.cashier' : 'checkout_images.party';

        // Get current session data or empty array
        $images = session($sessionKey, []);

        // Check if user entry already exists
        $found = false;

        foreach ($images as &$img) {
            if ($img['user_id'] == $userId) {
                // Update the corresponding image path
                if ($type === 'product') {
                    $img['product_image_path'] = $path;
                    $img['filename'] = $customFileName;
                } elseif ($type === 'user') {
                    $img['user_image_path'] = $path;
                    $img['filename'] = $customFileName;
                }
                $found = true;
                break;
            }
        }
        unset($img); // good practice after foreach by reference

        // If not found, add new entry
        if (!$found) {
            $images[] = [
                'user_id' => $userId,
                'type' => $type,
                'filename' => $customFileName,
                'product_image_path' => $type === 'product' ? $path : '',
                'user_image_path' => $type === 'user' ? $path : '',
            ];
        }

        // Save back to session
        session()->put($sessionKey, $images);


        return response()->json([
            'success' => true,
            'filename' => $customFileName,
            'path' => $path,
            'orignal_path' => Storage::url($path), // This gives you the full URL

        ]);
    }


    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request)
    {
        $id = $request->id;
        $product = Product::findOrFail($id);

        $request->validate([

            'name'             => 'required|string|max:255',
            'category_id'      => 'required',
            'subcategory_id'   => 'required',
            'size'             => 'required|string|max:255',
            'cost_price'       => 'required|numeric|min:0',
            'sell_price'       => 'required|numeric|min:0',
            'discount_price'   => 'nullable|numeric|min:0|lte:sell_price',
            'reorder_level'    => 'nullable|numeric|min:0',
            'description'      => 'nullable|string',
        ]);

        $image = $product->image;
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image && file_exists(public_path('storage/product_images/' . $product->image))) {
                unlink(public_path('storage/product_images/' . $product->image));
            }

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('product_images', 'public');

                $image = $imagePath;
            }

            // $file = $request->file('image');
            // $imageName = time() . '_' . $file->getClientOriginalName();
            // $file->move(public_path('uploads/products'), $imageName);
            // $product->image = $imageName;
        }

        // Update product fields
        $product->name           = $request->name;
        // $product->brand          = $request->name;
        $product->category_id    = $request->category_id;
        $product->subcategory_id = $request->subcategory_id;
        $product->size           = $request->size;
        $product->cost_price     = $request->cost_price;
        $product->sell_price     = $request->sell_price;
        $product->discount_price = $request->discount_price;
        $product->reorder_level  = $request->reorder_level;
        $product->barcode    = $request->barcode;
        $product->mrp    = $request->mrp;
        $product->description    = $request->description;
        // $product->image    = $image;

        $product->save();

        return redirect()->route('products.list')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    // Soft delete a record
    public function destroy(Request $request)
    {
        $id = $request->id;

        // Check if the product is still associated with any invoices (items field)
        $invoices = DB::table('invoices')
            ->whereRaw("JSON_CONTAINS(items, '[{\"product_id\": $id}]')")
            ->first();

        // Check if the product's quantity is greater than zero in the inventories
        $inventory = DB::table('inventories')
            ->where('product_id', $id)
            ->where('quantity', '>', 0)
            ->first();

        // Check if the product's quantity is greater than zero in the demand_order_products
        $demandOrderProducts = DB::table('demand_order_products')
            ->where('product_id', $id)
            ->where('quantity', '>', 0)
            ->first();

        // If there are invoices associated or if the product still has inventory or demand orders with quantity > 0
        if (!empty($invoices)) {
            return response()->json(['status' => 'error', 'message' => "This product cannot be deleted because it is associated with invoices."]);
        }

        if (!empty($inventory)) {
            return response()->json(['status' => 'error', 'message' => "This product cannot be deleted because it has inventory with quantity greater than zero."]);
        }

        if (!empty($demandOrderProducts)) {
            return response()->json(['status' => 'error', 'message' => "This product cannot be deleted because it is associated with demand orders with quantity greater than zero."]);
        }

        // Soft delete the product by updating the `is_deleted` field
        $product = Product::findOrFail($id);
        $product->update(['is_deleted' => 'yes']);

        // Update the quantity in inventories and demand_order_products to zero (if the product is deleted)
        DB::table('inventories')
            ->where('product_id', $id)
            ->update(['quantity' => 0]);

        DB::table('demand_order_products')
            ->where('product_id', $id)
            ->update(['quantity' => 0]);

        return redirect()->route('users.list')->with('success', 'Product has been deleted successfully.');
    }


    public function getAvailabilityBranch($productId, Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');


        $from_count = Branch::with(['inventories' => function ($query) use ($productId) {
            $query->where('product_id', $productId);
        }])
            ->where('id', $from) // Filter the branch here
            ->where('is_deleted', 'no')
            ->where('is_active', 'yes')
            ->get()
            ->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'available_quantity' => $branch->inventories->sum('quantity'),
                ];
            });

        $from_count_val = '';
        if (!empty($from_count)) {
            $from_count_val = $from_count[0]['available_quantity'];
        }


        $to_count = Branch::with(['inventories' => function ($query) use ($productId) {
            $query->where('product_id', $productId);
        }])
            ->where('id', $to) // Filter the branch here
            ->where('is_deleted', 'no')
            ->where('is_active', 'yes')
            ->get()
            ->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'available_quantity' => $branch->inventories->sum('quantity'),
                ];
            });

        $to_count_val = '';
        if (!empty($to_count)) {
            $to_count_val = $to_count[0]['available_quantity'];
        }

        $arr = [];
        $arr['from_count'] = $from_count_val;
        $arr['to_count'] = $to_count_val;

        return response()->json($arr);
    }

    public function sampleFileDownload()
    {

        $filePath = 'product_sample.csv'; // Stored in storage/app/public
        $fileName = 'product_sample.csv';

        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'Sample file not found.');
        }

        return Storage::disk('public')->download($filePath, $fileName);
    }

    public function statusChange(Request $request)
    {
        $user = Product::findOrFail($request->id);
        $user->is_active = $request->status;
        $user->save();

        return response()->json(['message' => 'Status updated successfully']);
    }

    public function view($id)
    {
        $product = Product::with(['category', 'subcategory'])->findOrFail($id);

        return response()->json($product);
    }
}
