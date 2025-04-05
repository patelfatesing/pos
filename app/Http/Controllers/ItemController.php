<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::all();
        //pre($items);
        return view('items.index', compact('items'));
    }
    public function cart()
    {
        $items = Item::all();
        //pre($items);
        return view('items.cart', compact('items'));
    }

    public function create()
    {
        return view('items.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
        ]);

        Item::create($request->all());

        return redirect()->route('items.index')->with('success', 'Item created successfully.');
    }

    public function show(Item $item)
    {
         $item = $item->first();
        //pre($item);
        return view('items.show', compact('item'));
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

        $query = Item::query();

        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('role_name', 'like', '%' . $searchValue . '%');
            });
        }

        $recordsTotal = Item::count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];

        $url = url('/');
        foreach ($data as $employee) {

            $action = "";
            $action .= "<a href='" . $url . "/role/edit/" . $employee->id . "' class='btn btn-info mr_2'>Edit</a>";
            $action .= '<button type="button" onclick="delete_role(' . $employee->id . ')" class="btn btn-danger ml-2">Delete</button>';

            $records[] = [
                'role_name' => $employee->role_name,
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
    public function edit(Item $item)
    {
        return view('items.edit', compact('item'));
    }

    public function update(Request $request, Item $item)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
        ]);

        $item->update($request->all());

        return redirect()->route('items.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
    }
}
