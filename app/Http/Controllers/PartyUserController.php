<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partyuser;
use App\Models\PartyUserImage;
use Illuminate\Support\Facades\DB;
use App\Models\PartyCustomerProductsPrice;
use Illuminate\Support\Facades\Auth;
use App\Models\CommissionUserImage;
use App\Models\Invoice;
use Illuminate\Support\Facades\Validator;

class PartyUserController extends Controller
{
    public function index()
    {
        $partyUsers = Partyuser::with('images')->latest()->get();
        return view('party_users.index', compact('partyUsers'));
    }

    public function getData(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $request->input('columns.' . $orderColumnIndex . '.data', 'id');
        $orderDirection = $request->input('order.0.dir', 'asc');

        // $query = Partyuser::with('images')->where('status', 'Active');
        $query = Partyuser::with('images');

        // **Search filter**
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('first_name', 'like', '%' . $searchValue . '%')
                    // ->orWhere('last_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('credit_points', 'like', '%' . $searchValue . '%');
            });
        }

        $query->where('is_delete', 'no');

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $recordsTotal = Partyuser::count();
        $recordsFiltered = $query->count();

        // pagination: only when length > 0
        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        $data = $query->orderBy($orderColumn, $orderDirection)->get();
        // $data = $query->orderBy($orderColumn, $orderDirection)
        //     ->offset($start)
        //     ->limit($length)
        //     ->get();

        $records = [];

        foreach ($data as $partyUser) {

            $images = $partyUser->images->map(function ($image) {
                return asset('storage/' . $image->image_path);
            })->toArray();

            $action = '';


            $action .= '<div class="d-flex align-items-center list-action">';

            if ($partyUser->use_credit > 0) {
                $action .= ' <a class="badge bg-primary mr-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Inline Price Change"'
                    . ' href="#" onclick="set_due_date(' . (int)$partyUser->id . ')"><i class="ri-calendar-event-fill"></i></a>';
            }
            $action .= '<a class="badge badge-primary mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"
                    href="' . url('/cust-product-price-change/form?id=' . $partyUser->id) . '"><i class="ri-currency-fill"></i></a>
            <a class="badge bg-info mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"
                                        href="' . url('/party-users/view/' . $partyUser->id) . '"><i class="ri-eye-line mr-0"></i></a>
            <a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"
                                        href="' . url('/party-users/edit/' . $partyUser->id) . '"><i class="ri-pencil-line mr-0"></i></a>
              <a class="badge bg-danger mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"
                                        href="#" onclick="delete_party_user(' . $partyUser->id . ')"><i class="ri-delete-bin-line mr-0"></i></a>
             </div>';


            $records[] = [
                'first_name' => $partyUser->first_name,
                'email' => $partyUser->email,
                'phone' => $partyUser->phone,
                'credit_points' => $partyUser->credit_points,
                'images' => implode(', ', array_map(function ($image) {
                    return "<img src='{$image}' alt='Image' style='width:50px;height:50px;'>";
                }, $images)),
                'status' => $partyUser->status == 'Active'
                    ? '<span onclick=\'statusChange("' . $partyUser->id . '", "Inactive")\'><div class="badge badge-success" style="cursor:pointer">Active</div></span>'
                    : '<span onclick=\'statusChange("' . $partyUser->id . '", "Active")\'><div class="badge badge-danger" style="cursor:pointer">Inactive</div></span>',
                // 'is_delete' => ($partyUser->is_delete=="No" ? '<div class="badge badge-success">Not Deleted</div>' : '<div class="badge badge-danger">Deleted</div>'),
                'use_credit' => $partyUser->use_credit,
                'created_at' => date('d-m-Y h:i', strtotime($partyUser->created_at)),
                'updated_at' => date('d-m-Y h:i', strtotime($partyUser->updated_at)),
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

    public function create()
    {
        return view('party_users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255|unique:party_users,first_name',
            'email' => 'nullable|email|max:255|unique:party_users,email',
            'phone' => 'nullable|digits:10|regex:/^[0-9]+$/|unique:party_users,phone',
            'address' => 'nullable|string|max:255',
            'credit_points' => 'required|numeric|min:0|max:99999999.99',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ], [
            'first_name.required' => 'Customer name is required.',
            'first_name.unique' => 'Customer name has already been taken.',
            // 'phone.required' => 'Mobile number is required.',
            'phone.digits' => 'Mobile number must be exactly 10 digits.',
            'phone.unique' => 'This mobile number is already in use.',
        ]);

        // Handle image upload
        // if ($request->hasFile('photo')) {
        //     $data['photo'] = $request->file('photo')->store('party_user_photos', 'public');
        // }
        $PartyUser = PartyUser::create($data);
        if ($request->hasFile('photo')) {
            $extension = $request->file('photo')->getClientOriginalExtension();
            $filename = $PartyUser->id . '_partyuser.' . $extension;

            // Store new photo
            $photoPath = $request->file('photo')->storeAs('party_user_photos', $filename, 'public');

            // Update PartyUser photo field
            $PartyUser->photo = $photoPath;
            $PartyUser->save();
        }

        return redirect()->route('party-users.list')->with('success', 'Party User Created');
    }

    public function edit($id)
    {
        $partyUser = Partyuser::with('images')->where('id', $id)->firstOrFail();
        return view('party_users.edit', compact('partyUser'));
    }

    public function view($id)
    {
        $partyUser = Partyuser::with('images')->where('id', $id)->firstOrFail();
        return view('party_users.view', compact('partyUser'));
    }

    public function update(Request $request, Partyuser $Partyuser)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255|unique:party_users,first_name,' . $Partyuser->id,
            'email' => 'nullable|email|max:255|unique:party_users,email,' . $Partyuser->id,
            'phone' => 'nullable|digits:10|regex:/^[0-9]+$/|unique:party_users,phone,' . $Partyuser->id,
            'address' => 'nullable|string|max:255',
            'credit_points' => 'required|numeric|min:0|max:99999999.99',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($Partyuser->photo) {
                \Storage::disk('public')->delete($Partyuser->photo);
            }
            $extension = $request->file('photo')->getClientOriginalExtension();
            $filename = $Partyuser->id . '_partyuser' . '.' . $extension;

            // Store new photo
            $data['photo'] = $request->file('photo')->storeAs('party_user_photos', $filename, 'public');
        }

        $Partyuser->update($data);

        return redirect()->route('party-users.list')->with('success', 'Party User Updated');
    }

    public function destroy($id)
    {

        $tras = Invoice::where('party_user_id', $id)->first();

        if (!empty($tras)) {
            return response()->json(['status' => 'error', 'message' => "This Party user can'n delete."]);
        }
        // Find the user and soft delete
        $record = Partyuser::findOrFail($id);
        $record->is_delete = "Yes";
        $record->save();
        return response()->json(['success' => true, 'message' => 'Party User has been deleted successfully.']);
    }

    public function custProductPriceChangeForm(Request $request)
    {
        $id = request()->id ?? null;
        $partyUser = Partyuser::select('first_name', 'id')->where('id', $id)->first();
        $subcategories = DB::table('sub_categories')->where('is_deleted', 'no')->get();
        $partyUserAll = Partyuser::select('first_name', 'id')->get();

        $subcategoryId = request()->subcategory_id ?? null;

        $products = DB::table('products')
            ->select(
                'products.id',
                'products.name',
                'products.mrp',
                'products.sell_price',
                DB::raw('IFNULL(party_customer_products_price.cust_discount_price, 0) as cust_discount_price') // Default to 0 if no discount
            )
            ->leftJoin('party_customer_products_price', function ($join) use ($id) {
                $join->on('products.id', '=', 'party_customer_products_price.product_id')
                    ->where('party_customer_products_price.party_user_id', $id);
            })
            ->when($subcategoryId, function ($query, $subcategoryId) {
                $query->where('subcategory_id', $subcategoryId);
            })
            ->where('products.is_deleted', 'no')
            ->where('products.is_active', 'yes')
            ->groupBy(
                'products.id',
                'products.name',
                'products.sell_price',
                'products.mrp',
                'party_customer_products_price.cust_discount_price',
            )
            ->get();

        // $products = DB::table('products')
        //     ->select(
        //         'products.id',
        //         'products.name',
        //         'products.mrp',
        //         'products.sell_price',
        //         DB::raw('IFNULL(party_customer_products_price.cust_discount_price, 0) as cust_discount_price'), // Default to 0 if no discount
        //         'inventories.quantity',
        //         'inventories.store_id',
        //         'inventories.location_id',
        //         'inventories.batch_no',
        //         'inventories.expiry_date'
        //     )
        //     ->leftJoin('party_customer_products_price', function ($join) use ($id) {
        //         $join->on('products.id', '=', 'party_customer_products_price.product_id')
        //             ->where('party_customer_products_price.party_user_id', $id);
        //     })
        //     ->leftJoin('inventories', 'products.id', '=', 'inventories.product_id') // Join with inventories table
        //     ->when($subcategoryId, function ($query, $subcategoryId) {
        //         $query->where('subcategory_id', $subcategoryId);
        //     })
        //     ->where('products.is_deleted', 'no')
        //     ->where('products.is_active', 'yes')
        //     ->where('inventories.quantity', '>', 0) // Filter inventories where quantity > 0
        //     ->groupBy(
        //         'products.id',
        //         'products.name',
        //         'products.sell_price',
        //         'products.mrp',
        //         'party_customer_products_price.cust_discount_price',
        //         'inventories.quantity',
        //         'inventories.store_id',
        //         'inventories.location_id',
        //         'inventories.batch_no',
        //         'inventories.expiry_date'
        //     )
        //     ->get();



        return view('party_users.product-form', compact('products', 'partyUser', 'subcategories', 'partyUserAll'));

        return response()->json(['error' => 'Form not found'], 404);
    }

    public function custPriceChange(Request $request)
    {

        $validated = $request->validate([
            'cust_user_id' => 'required|exists:party_users,id',
            'items' => 'required|array',
            'items.*.mrp' => 'required|numeric|min:1',
            'items.*.cust_discount_price' => 'required|numeric|min:1',
            'notes' => 'nullable|string',
        ]);

        // Manually check each item's discount price
        $errors = [];
        // foreach ($request->items as $id => $item) {
        //     if ($item['cust_discount_price'] > $item['mrp']) {
        //         $errors["items.$id.cust_discount_price"] = ['Discount price must be less than or equal to sell price.'];
        //     }
        // }

        if (!empty($errors)) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $errors,
            ], 422);
        }

        $cust_user_id = $request->cust_user_id;
        DB::beginTransaction();

        try {
            // Perform your business logic (e.g., saving data, etc.)

            foreach ($request['items'] as $key => $item) {

                $cust_pro = PartyCustomerProductsPrice::where('product_id', $key)->where('party_user_id', $cust_user_id)->first();

                $cust_discount_price = 0;
                $cust_discount_amt = 0;

                if ($item['cust_discount_price'] == $item['mrp']) {
                    $cust_discount_price = $item['mrp'];
                    $cust_discount_amt = 0;
                } else {
                    $cust_discount_price = $item['cust_discount_price'];
                    $cust_discount_amt = $item['mrp'] - $item['cust_discount_price'];
                }

                if (!empty($cust_pro)) {
                    $cust_pro->update([
                        'cust_discount_price' => $cust_discount_price,
                        'cust_discount_amt' => $cust_discount_amt,
                        'status' => 'active',
                        'updated_by' => Auth::id()
                    ]);
                } else {
                    PartyCustomerProductsPrice::create([
                        'party_user_id' => $cust_user_id,
                        'product_id' => $key,
                        'cust_discount_price' => $cust_discount_price,
                        'cust_discount_amt' => $cust_discount_amt,
                        'status' => 'active',
                        'created_by' => Auth::id()
                    ]);
                }
            }

            DB::commit();
            // If validation passes, send success response
            if ($request->ajax()) {
                return response()->json(['success' => 'Price changes submitted successfully!']);
            }

            return back()->with('success', 'Price change submitted successfully!');
        } catch (\Exception $e) {
            // Handle exceptions and send error response
            if ($request->ajax()) {
                return response()->json(['error' => 'An error occurred: ' . $e->getMessage()]);
            }

            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function getDataCommission(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDirection = $request->input('order.0.dir', 'desc');

        $columns = [
            'invoice_id',
            'invoice_number',
            'invoice_date',
            'invoice_total',
            'commission_amount',
            'commission_user_id',
            'commission_user_name',
            'commission_type',
            'commission_value',
            'applies_to',
            'start_date',
            'end_date'
        ];

        $orderColumn = $columns[$orderColumnIndex] ?? 'invoice_date';
        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }

        // Base query
        $query = DB::table('invoices as i')
            ->select(
                'i.id as invoice_id',
                'i.invoice_number',
                'i.created_at as invoice_date',
                'i.total as invoice_total',
                'i.creditpay as commission_amount',
                'cu.id as party_user_id',
                'cu.first_name as commission_user_name',
                'cu.credit_points',
                'ch.total_purchase_items',
                'ch.credit_amount',
                'ch.status',
                'ch.id as commission_id',
                'pi.image_path',
                'pi.id as party_user_image_id',
                'pi.transaction_id',
                'pi.type',
            )
            ->leftJoin('credit_histories as ch', 'i.id', '=', 'ch.invoice_id')
            ->leftJoin('party_users as cu', 'ch.party_user_id', '=', 'cu.id')
            ->leftJoin('party_images as pi', 'i.id', '=', 'pi.transaction_id')
            ->whereNotNull('i.party_user_id');

        // Total record count before filters
        $recordsTotal = DB::table('invoices')
            ->whereNotNull('party_user_id')
            ->count();

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('i.invoice_number', 'like', "%$searchValue%")
                    ->orWhere('cu.first_name', 'like', "%$searchValue%");
            });
        }

        if ($request->customer_id) {
            $query->where('cu.id', $request->customer_id);
        }

        // Count after filters
        $recordsFiltered = $query->count();

        // Get data with order and pagination
        $data = $query
            ->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
    public function getCreditHistory(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDirection = $request->input('order.0.dir', 'desc');

        $columns = [
            'invoice_id',
            'invoice_number',
            'invoice_date',
            'invoice_total',
            'commission_amount',
            'commission_user_id',
            'commission_user_name',
            'commission_type',
            'commission_value',
            'applies_to',
            'start_date',
            'end_date',
            'debit_amount'
        ];

        $orderColumn = $columns[$orderColumnIndex] ?? 'invoice_date';
        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }

        // Base query
        $query = DB::table('credit_histories as ch')
            ->select(
                'i.id as invoice_id',
                'i.invoice_number',
                'i.created_at as invoice_date',
                'i.total as invoice_total',
                'i.creditpay as commission_amount',
                'cu.id as party_user_id',
                'cu.first_name as commission_user_name',
                'cu.credit_points',
                'ch.total_purchase_items',
                'ch.credit_amount',
                'ch.debit_amount',
                'ch.status',
                'ch.created_at',
                'ch.id as commission_id',
                'pi.image_path',
                'pi.id as party_user_image_id',
                'pi.transaction_id',
                'pi.type',
            )
            ->leftJoin('invoices as i', 'ch.invoice_id', '=', 'i.id')
            ->leftJoin('party_users as cu', 'ch.party_user_id', '=', 'cu.id')
            ->leftJoin('party_images as pi', 'ch.invoice_id', '=', 'pi.transaction_id');



        // Total record count before filters
        $recordsTotal = DB::table('invoices')
            ->whereNotNull('party_user_id')
            ->count();

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('i.invoice_number', 'like', "%$searchValue%")
                    ->orWhere('cu.first_name', 'like', "%$searchValue%");
            });
        }

        if ($request->customer_id) {
            $query->where('cu.id', $request->customer_id);
        }

        // Count after filters
        $recordsFiltered = $query->count();

        // Get data with order and pagination
        $data = $query
            ->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function custTrasactionPhoto($id, Request $request)
    {
        $imageType = $request->get('imageType');
        $invoice_id = $request->get('invoice_id');


        if ($imageType == "Commission") {
            $photos = CommissionUserImage::where('transaction_id', $invoice_id)->where('commission_user_id', $id)->first();
        } else {
            $photos = PartyUserImage::where('transaction_id', $id)->first();
        }

        return view('party_users.cust-photo', compact('photos', 'imageType'));
    }

    public function statusChange(Request $request)
    {
        $user = Partyuser::findOrFail($request->id);
        $user->status = $request->status;
        $user->save();

        return response()->json(['message' => 'Status updated successfully']);
    }

    // In YourController.php
    public function getCredit($partyUserId)
    {
        $partyUser = PartyUser::findOrFail($partyUserId);
        return response()->json([
            'credit' => $partyUser->credit_points,
            'left_credit' => $partyUser->left_credit,
            'use_credit' => $partyUser->use_credit  // Assuming 'credit_points' is the field in your PartyUser model
        ]);
    }

    public function getCustomerDiscount($partyUserId, $productId)
    {
        // Fetch the discount from the party_customer_products_price table
        $discount = PartyCustomerProductsPrice::where('party_user_id', $partyUserId)
            ->where('product_id', $productId)
            ->first();

        // Check if discount is found and return the response
        if ($discount) {
            return response()->json([
                'discount' => $discount->cust_discount_price // Adjust according to your column name
            ]);
        } else {
            // Return a default discount price or an error message
            return response()->json([
                'discount' => 0 // Default discount if not found
            ]);
        }
    }

    public function setDueDate(Request $request)
    {
        // 🔍 Validate input
        $validator = Validator::make($request->all(), [
            'due_date' => 'required'
        ]);

        $id = $request->party_user_id;

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // 🧱 Find the product
        $product = Partyuser::findOrFail($id);
        // 🔁 Update product
        $product->due_date = $request->due_date;
        $product->save();

       
            // sendNotification('price_change', $product->name . ' Product price is changed.', $store->id, Auth::id(), json_encode(['id' => (string)$his_data->id]), 0);
        

        return response()->json([
            'status' => 'success',
            'message' => 'Party user due date set has been successfully.',
            'data' => $product,
        ]);
    }
}
