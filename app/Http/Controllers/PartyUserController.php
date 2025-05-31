<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partyuser;
use App\Models\PartyUserImage;
use Illuminate\Support\Facades\DB;
use App\Models\PartyCustomerProductsPrice;
use Illuminate\Support\Facades\Auth;
use App\Models\CommissionUserImage;

class PartyUserController extends Controller
{
    public function index()
    {
        $partyUsers = Partyuser::with('images')->where('status', 'Active')->latest()->get();
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

        $query = Partyuser::with('images')->where('status', 'Active');

        // **Search filter**
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('first_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('last_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('credit_points', 'like', '%' . $searchValue . '%');
            });
        }

        $recordsTotal = Partyuser::where('status', 'Active')->count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];

        foreach ($data as $partyUser) {

            $images = $partyUser->images->map(function ($image) {
                return asset('storage/' . $image->image_path);
            })->toArray();

            $first_name = '<div class="d-flex align-items-center list-action"><a class="badge bg-info mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit" href="' . url('/party-users/view/' . $partyUser->id) . '">' . $partyUser->first_name . '</a></div>';
            $last_name = '<div class="d-flex align-items-center list-action"><a class="badge bg-info mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit" href="' . url('/party-users/view/' . $partyUser->id) . '">' . $partyUser->last_name . '</a></div>';

            $action = '<div class="d-flex align-items-center list-action">
            <a class="badge badge-primary mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"
                    href="#" onclick="party_cust_price_change(' . $partyUser->id . ')"><i class="ri-eye-line mr-0"></i></a>
                    
            <a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"
                                        href="' . url('/party-users/edit/' . $partyUser->id) . '"><i class="ri-pencil-line mr-0"></i></a>
                                  
            </div>';
            //   $action = '<div class="d-flex align-items-center list-action">
            // <a class="badge badge-primary mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"
            //         href="#" onclick="party_cust_price_change(' . $partyUser->id . ')"><i class="ri-eye-line mr-0"></i></a>

            // <a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"
            //                             href="' . url('/party-users/edit/' . $partyUser->id) . '"><i class="ri-pencil-line mr-0"></i></a>
            //                         <a class="badge bg-warning mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"
            //                             href="#" onclick="delete_party_user(' . $partyUser->id . ')"><i class="ri-delete-bin-line mr-0"></i></a>
            // </div>';


            $records[] = [
                'first_name' => $first_name,
                'last_name' => $last_name,
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:party_users,email',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'credit_points' => 'required|numeric|min:0|max:99999999.99',
            'images.*' => 'nullable|image|max:2048',

        ]);
        $partyUser = Partyuser::create($data);

        // Save images if any
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('commission_images', 'public');
                PartyUserImage::create([
                    'party_user_id' => $partyUser->id,
                    'image_path' => $path,
                    'type' => $image->getClientOriginalName(),
                ]);
            }
        }

        return redirect()->route('party-users.list')->with('success', 'Party User Created');
    }

    public function edit($id)
    {
        $partyUser = Partyuser::with('images')->where('id', $id)->where('status', 'Active')->firstOrFail();
        return view('party_users.edit', compact('partyUser'));
    }

    public function view($id)
    {
        $partyUser = Partyuser::with('images')->where('id', $id)->where('status', 'Active')->firstOrFail();
        return view('party_users.view', compact('partyUser'));
    }

    public function update(Request $request, Partyuser $Partyuser)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:party_users,email,' . $Partyuser->id . ',id',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'credit_points' => 'required|numeric|min:0|max:99999999.99',
            'images.*' => 'nullable|image|max:2048',
        ]);

        $Partyuser->update($data);

        if ($request->hasFile('images')) {
            // Delete old images
            foreach ($Partyuser->images as $image) {
                \Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }

            // Save new images
            foreach ($request->file('images') as $image) {
                $path = $image->store('party_images', 'public');
                PartyUserImage::create([
                    'party_user_id' => $Partyuser->id,
                    'image_path' => $path,
                    'type' => $image->getClientOriginalName(),
                ]);
            }
        }

        return redirect()->route('party-users.list')->with('success', 'Party User Updated');
    }

    public function destroy($id)
    {
        // Find the user and soft delete
        $record = Partyuser::where('status', 'Active')->findOrFail($id);
        $record->is_delete = "Yes";
        $record->save();

        //return redirect()->route('users.list')->with('success', 'Party User has been deleted successfully.');
    }

    public function custProductPriceChangeForm($id)
    {
        $partyUser = Partyuser::select('first_name', 'last_name', 'id')->where('status', 'Active')->where('id', $id)->first();

        $products = DB::table('products')
            ->select(
                'products.id',
                'products.name',
                'products.sell_price',
                DB::raw('IFNULL(party_customer_products_price.cust_discount_price, 0) as cust_discount_price') // Default to 0 if no discount
            )
            ->leftJoin('party_customer_products_price', function ($join) use ($id) {
                $join->on('products.id', '=', 'party_customer_products_price.product_id')
                    ->where('party_customer_products_price.party_user_id', $id);
            })
            ->where('products.is_deleted', 'no')
            ->where('products.is_active', 'yes')
            ->groupBy(
                'products.id',
                'products.name',
                'products.sell_price',
                'party_customer_products_price.cust_discount_price',
            )
            ->get();

        return view('party_users.product-form', compact('products', 'partyUser'));

        return response()->json(['error' => 'Form not found'], 404);
    }

    public function custPriceChange(Request $request)
    {

        $validated = $request->validate([
            'cust_user_id' => 'required|exists:party_users,id',
            'items' => 'required|array',
            'items.*.sell_price' => 'required|numeric|min:1',
            'items.*.cust_discount_price' => 'required|numeric|min:1',
            'notes' => 'nullable|string',
        ]);

        // Manually check each item's discount price
        $errors = [];
        foreach ($request->items as $id => $item) {
            if ($item['cust_discount_price'] > $item['sell_price']) {
                $errors["items.$id.cust_discount_price"] = ['Discount price must be less than or equal to sell price.'];
            }
        }

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

                $cust_pro = PartyCustomerProductsPrice::where('id', $key)->where('party_user_id', $cust_user_id)->first();

                $cust_discount_price = 0;
                $cust_discount_amt = 0;

                if ($item['cust_discount_price'] == $item['sell_price']) {
                    $cust_discount_price = $item['sell_price'];
                    $cust_discount_amt = 0;
                } else {
                    $cust_discount_price = $item['cust_discount_price'];
                    $cust_discount_amt = $item['sell_price'] - $item['cust_discount_price'];
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
                DB::raw("CONCAT(cu.first_name, ' ', cu.last_name) as commission_user_name"),
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
                    ->orWhere(DB::raw("CONCAT(cu.first_name, ' ', cu.last_name)"), 'like', "%$searchValue%");
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
        $imageType=$request->get('imageType');
        $invoice_id=$request->get('invoice_id');

    
        if($imageType=="Commission"){
            $photos = CommissionUserImage::where('transaction_id', $invoice_id)->where('commission_user_id', $id)->first();
        }else{
            $photos = PartyUserImage::where('transaction_id', $id)->first();
        }

        return view('party_users.cust-photo', compact('photos','imageType'));
    }

    public function statusChange(Request $request)
    {
        $user = Partyuser::where('status', 'Active')->findOrFail($request->id);
        $user->status = $request->status;
        $user->save();

        return response()->json(['message' => 'Status updated successfully']);
    }
}
