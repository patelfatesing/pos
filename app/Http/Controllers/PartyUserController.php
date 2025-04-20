<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partyuser;
use App\Models\PartyUserImage;
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

        $query = Partyuser::with('images');

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

        $recordsTotal = Partyuser::count();
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

            $action ='<div class="d-flex align-items-center list-action">
            <a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"
                                        href="' . url('/party-users/edit/' . $partyUser->id) . '"><i class="ri-pencil-line mr-0"></i></a>
                                    <a class="badge bg-warning mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"
                                        href="#" onclick="delete_party_user(' . $partyUser->id . ')"><i class="ri-delete-bin-line mr-0"></i></a>
            </div>';

            $records[] = [
                'first_name' => $partyUser->first_name,
                'middle_name' => $partyUser->middle_name,
                'last_name' => $partyUser->last_name,
                'email' => $partyUser->email,
                'phone' => $partyUser->phone,
                'credit_points' => $partyUser->credit_points,
                'images' => implode(', ', array_map(function ($image) {
                    return "<img src='{$image}' alt='Image' style='width:50px;height:50px;'>";
                }, $images)),
                
                'created_at' => date('d-m-Y h:i', strtotime($partyUser->created_at)),
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
            'middle_name' => 'nullable|string|max:255',
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
        $partyUser = Partyuser::with('images')->where('id', $id)->firstOrFail();
        return view('party_users.edit', compact('partyUser'));
    }
    public function update(Request $request, Partyuser $Partyuser)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
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

    public function destroy(Request $request)
    {
        $id = $request->id;
        // Find the user and soft delete
        $record = Partyuser::findOrFail($id);
        $record->update(['is_deleted' => 'yes']);

        return redirect()->route('users.list')->with('success', 'Party User has been deleted successfully.');
    }
}
