<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountGroup;
use App\Models\Accounting\AccountSubGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    public function index()
    {
        $groups = AccountGroup::with('parent')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('accounting.groups.index', compact('groups'));
    }

    public function getData(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $searchValue       = $request->input('search.value', '');
        $orderColumnIndex  = (int) $request->input('order.0.column', 0);
        // ✅ Fix the bug here (use dot-notation, not string concat):
        $orderColumn       = $request->input("columns.$orderColumnIndex.data", 'name');
        $orderDirection    = $request->input('order.0.dir', 'asc') === 'desc' ? 'desc' : 'asc';

        // Base query with left join to parent for searching/sorting parent name
        $base = AccountGroup::query()
            ->leftJoin('account_groups as p', 'p.id', '=', 'account_groups.parent_id')
            ->select([
                'account_groups.id',
                'account_groups.name',
                'account_groups.nature',
                'account_groups.affects_gross',
                'account_groups.parent_id',
                'account_groups.sort_order',
                'account_groups.created_at',
                'account_groups.updated_at',
                'account_groups.is_user_defined',
                DB::raw('COALESCE(p.name, "-") as parent_name'),
            ]);

        // If you have soft delete or is_deleted field, filter here (uncomment if needed)
        // $base->where('account_groups.is_deleted', 'no');

        // Total before filtering
        $recordsTotal = (clone $base)->count();

        // Search
        if (!empty($searchValue)) {
            $base->where(function ($q) use ($searchValue) {
                $q->where('account_groups.name', 'like', "%{$searchValue}%")
                    ->orWhere('account_groups.nature', 'like', "%{$searchValue}%")
                    ->orWhere('p.name', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $base)->count();

        // Whitelist sortable columns
        $sortable = [
            'name'         => 'account_groups.name',
            'nature'       => 'account_groups.nature',
            'affects_gross' => 'account_groups.affects_gross',
            'parent'       => 'parent_name',
            'sort_order'   => 'account_groups.sort_order',
            'created_at'   => 'account_groups.created_at',
            'updated_at'   => 'account_groups.updated_at',
        ];
        $orderByCol = $sortable[$orderColumn] ?? 'account_groups.name';

        // Fetch page
        $rows = $base
            ->orderBy($orderByCol, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        // Build response rows
        $records = [];
        foreach ($rows as $g) {
            $affectsBadge = $g->affects_gross
                ? '<span class="badge bg-success">Yes</span>'
                : '<span class="badge bg-secondary">No</span>';

            $actions = '';

            // Only allow edit/delete if user-defined
            if ($g->is_user_defined == '1') {
                $actions .= '
            <div class="d-flex align-items-center gap-1">
              <a href="' . route('accounting.groups.edit', $g->id) . '" class="btn btn-sm btn-warning mr-1">Edit</a>
              <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $g->id . '">Delete</button>
            </div>
        ';
            } else {
                $actions .= '<div class="text-muted">System</div>';
            }

            $records[] = [
                // keys must match columns.data in JS
                'name'          => e($g->name),
                'nature'        => e($g->nature),
                'affects_gross' => $affectsBadge,
                'parent'        => e($g->parent_name),
                'sort_order'    => (string) $g->sort_order,
                'action'        => $actions,
            ];
        }

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $records,
        ]);
    }

    public function create()
    {
        $parents = AccountGroup::orderBy('name')->get();
        $sub_parents = AccountSubGroup::orderBy('name')->get();
        return view('accounting.groups.create', compact('parents', 'sub_parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:191|unique:account_groups,name',
            'code'         => 'nullable|string|max:20',
            'nature'       => 'required|in:Asset,Liability,Income,Expense',
            'affects_gross' => 'nullable|boolean',
            'parent_id'    => 'nullable|exists:account_groups,id',
            'sort_order'   => 'nullable|integer',
        ]);

        AccountGroup::create($data);

        return redirect()->route('accounting.groups.list')
            ->with('success', 'Group created successfully.');
    }

    public function edit($id)
    {

        $parents = AccountGroup::where('id', '!=', $id)->orderBy('name')->get();
        $group = AccountGroup::where('id', $id)->orderBy('name')->firstOrFail();

        return view('accounting.groups.edit', compact('group', 'parents'));
    }

    public function update(Request $request)
    {


        $id = $request->id; // comes from hidden input or route

        $group = AccountGroup::findOrFail($id);

        $data = $request->validate([
            'name'          => 'required|string|max:191|unique:account_groups,name,' . $id,
            'code'          => 'nullable|string|max:20',
            'nature'        => 'required|in:Asset,Liability,Income,Expense',
            'affects_gross' => 'nullable|boolean',
            'parent_id'     => 'nullable|exists:account_groups,id',
            'sort_order'    => 'nullable|integer',
        ]);

        // prevent selecting itself as parent
        if (!empty($data['parent_id']) && (int)$data['parent_id'] === (int)$group->id) {
            return back()->withErrors(['parent_id' => 'A group cannot be its own parent.'])->withInput();
        }

        // normalize checkbox
        $data['affects_gross'] = $request->boolean('affects_gross');

        // track who updated it (if you have that column)
        $data['updated_by'] = auth()->id();

        $group->update($data);


        return redirect()->route('accounting.groups.list')
            ->with('success', 'Group updated successfully.');
    }

    public function children($id)
    {
        $subcategories = AccountSubGroup::where('group_id', $id)->get();
        return response()->json($subcategories);
    }

    public function destroy(AccountGroup $group)
    {
        if ($group->ledgers()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete: group has ledgers.']);
        }

        $group->delete();

        return redirect()->route('accounting.groups.index')
            ->with('success', 'Group deleted successfully.');
    }
}
