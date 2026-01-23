<?php

namespace App\Http\Controllers;

use App\Models\PackSize;
use App\Http\Requests\StorePackSizeRequest;
use App\Http\Requests\UpdatePackSizeRequest;
use Illuminate\Http\Request;

class PackSizeController extends Controller
{
    public function index()
    {
        $packSizes = PackSize::all();
        return view('pack_sizes.index', compact('packSizes'));
    }

    public function getData(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $request->input("columns.$orderColumnIndex.data", 'id'); // Fixed input key
        $orderDirection = $request->input('order.0.dir', 'asc');

        // Validate order direction
        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'asc';
        }

        $query = PackSize::query()->where('is_deleted', 'no');

        if (!empty($searchValue)) {
            $query->where('size', 'like', '%' . $searchValue . '%');
        }

        $recordsTotal = PackSize::where('is_deleted', 'no')->count();
        $recordsFiltered = $query->count();

        $roleId = auth()->user()->role_id;

        $userId = auth()->id();

        $listAccess = getAccess($roleId, 'categories-list');

        // ❌ No permission → return empty table
        if (in_array($listAccess, ['none', 'no'])) {
            return response()->json([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }

        // 👤 Own permission → only own products
        if ($listAccess === 'own') {
            $query->where('created_by', $userId);
        }

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];
        $url = url('/');

        foreach ($data as $role) {
            $action = '';
            // $action .= "<a href='" . $url . "/categories/edit/" . $role->id . "' class='btn btn-info mr-2'>Edit</a>";
            // $action .= '<button type="button" onclick="delete_category(' . $role->id . ')" class="btn btn-danger ml-2">Delete</button>';

            $records[] = [
                'size' => $role->size,
                'is_active' => ($role->is_active ? '<div class="badge badge-success">Active</div>' : '<div class="badge badge-success">Inactive</div>'),
                'created_at' => \Carbon\Carbon::parse($role->created_at)->format('d-m-Y H:i'),
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
        return view('pack_sizes.create');
    }

    public function store1(StorePackSizeRequest $request)
    {
        PackSize::create($request->validated());
        return redirect()->route('pack_sizes.index')->with('success', 'Pack Size created!');
    }

    public function store(Request $request)
    {
        // Step 1: Validate input (numeric only, required)
        $validator = \Validator::make(
            $request->all(),
            [
                'size' => 'required|numeric',
            ],
            [
                'size.required' => 'The size is required.',
                'size.numeric'  => 'Size must be a number.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Step 2: Append " ML"
        $sizeWithML = $request->size . ' ML';

        // Step 3: Check uniqueness AFTER adding ML
        $exists = \App\Models\PackSize::where('size', $sizeWithML)->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'size' => ['This size already exists.']
                ]
            ], 422);
        }

        // Step 4: Save
        \App\Models\PackSize::create([
            'size' => $sizeWithML,
        ]);

        // Step 5: Success response
        return response()->json([
            'status'  => 'success',
            'message' => 'Pack size added successfully'
        ]);
    }

    public function show(PackSize $packSize)
    {
        return view('pack_sizes.show', compact('packSize'));
    }

    public function edit(PackSize $packSize)
    {
        return view('pack_sizes.edit', compact('packSize'));
    }

    public function update(UpdatePackSizeRequest $request, PackSize $packSize)
    {
        $packSize->update($request->validated());
        return redirect()->route('pack_sizes.index')->with('success', 'Pack Size updated!');
    }

    public function destroy(PackSize $packSize)
    {
        $packSize->delete();
        return redirect()->route('pack_sizes.index')->with('success', 'Pack Size deleted!');
    }
}
