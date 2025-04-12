<?php

namespace App\Http\Controllers;

use App\Models\Commissionuser;
use App\Models\CommissionUserImage;
use Illuminate\Http\Request;

class CommissionUserController extends Controller
{
    public function index()
    {
        $commissionUsers = Commissionuser::with('images')->latest()->get();
        return view('commission_users.index', compact('commissionUsers'));
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

        $query = Commissionuser::with('images');

        // **Search filter**
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('first_name', 'like', '%' . $searchValue . '%')
                  ->orWhere('middle_name', 'like', '%' . $searchValue . '%')
                  ->orWhere('last_name', 'like', '%' . $searchValue . '%')
                  ->orWhere('commission_type', 'like', '%' . $searchValue . '%')
                  ->orWhere('applies_to', 'like', '%' . $searchValue . '%')
                  ->orWhere('commission_value', 'like', '%' . $searchValue . '%');
            });
        }

        $recordsTotal = Commissionuser::count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];

        foreach ($data as $commissionUser) {

            $images = $commissionUser->images->map(function ($image) {
                return asset('storage/' . $image->image_path);
            })->toArray();

            $records[] = [
                'first_name' => $commissionUser->first_name,
                'middle_name' => $commissionUser->middle_name,
                'last_name' => $commissionUser->last_name,
                'commission_type' => $commissionUser->commission_type,
                'commission_value' => $commissionUser->commission_value,
                'applies_to' => $commissionUser->applies_to,
                'is_active' => $commissionUser->is_active,
                'start_date' => $commissionUser->start_date,
                'end_date' => $commissionUser->end_date,
                'images' => implode(', ', array_map(function ($image) {
                    return "<img src='{$image}' alt='Image' style='width:50px;height:50px;'>";
                }, $images)),
                
                'created_at' => date('d-m-Y h:i', strtotime($commissionUser->created_at)),
                'action' => "<a href='" . url('/commission-users/edit/' . $commissionUser->id) . "' class='btn btn-info mr-2'>Edit</a>
                             <button type='button' onclick='delete_commission_user(" . $commissionUser->id . ")' class='btn btn-danger ml-2'>Delete</button>"
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
        return view('commission_users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:commission_users,email',
            'commission_type' => 'required|in:fixed,percentage',
            'commission_value' => 'required|numeric',
            'applies_to' => 'required|in:all,category,product',
            'reference_id' => 'nullable|integer',
            'is_active' => 'required|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'images.*' => 'nullable|image|max:2048',
        ]);

        $commissionUser = Commissionuser::create($data);

        // Save images if any
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('commission_images', 'public');
                CommissionUserImage::create([
                    'commission_user_id' => $commissionUser->id,
                    'image_path' => $path,
                    'image_name' => $image->getClientOriginalName(),
                ]);
            }
        }

        return redirect()->route('commission-users.list')->with('success', 'Commission User Created');
    }

    public function edit($id)
    {
        $commissionUser = Commissionuser::with('images')->where('id', $id)->firstOrFail();
        return view('commission_users.edit', compact('commissionUser'));
    }

public function update(Request $request, Commissionuser $Commissionuser)
    {
        //print_r($Commissionuser);exit;
       // $commissionUser = Commissionuser::findOrFail($id);

        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'commission_type' => 'required|in:fixed,percentage',
            'commission_value' => 'required|numeric',
            'applies_to' => 'required|in:all,category,product',
            'reference_id' => 'nullable|integer',
            'is_active' => 'required|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'images.*' => 'nullable|image|max:2048',
        ]);

        $Commissionuser->update($data);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('commission_images', 'public');
                CommissionUserImage::create([
                    'commission_user_id' => $Commissionuser->id,
                    'image_path' => $path,
                    'image_name' => $image->getClientOriginalName(),
                ]);
            }
        }

        return redirect()->route('commission-users.list')->with('success', 'Commission User Updated');
    }


    public function destroy(Commissionuser $Commissionuser)
    {
        $Commissionuser->delete();
        return response()->json(['success' => true, 'message' => 'Commission User Deleted']);
    }
}

