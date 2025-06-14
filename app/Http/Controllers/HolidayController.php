<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\Branch;                 // optional – only if you show branch names in forms
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class HolidayController extends Controller
{
    /**
     * Display a paginated, searchable list of holidays.
     */
    public function index(Request $request)
    {
        // Optional search filter
        $query = Holiday::query();

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhereDate('date', Carbon::parse($search)->format('Y-m-d'));
        }

        $holidays = $query
            ->with('branch')           // eager‑load branch name
            ->orderBy('date', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('holidays.index', compact('holidays'));
    }

    /**
     * Show the form for creating a new holiday.
     */
    public function create()
    {
        $branches = Branch::pluck('name', 'id');   // for a drop‑down
        return view('holidays.create', compact('branches'));
    }

    /**
     * Store a newly created holiday in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        Holiday::create($data);

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Holiday added successfully.');
    }

    /**
     * Show the form for editing the specified holiday.
     */
    public function edit(Holiday $holiday)
    {
        $branches = Branch::pluck('name', 'id');
        return view('holidays.edit', compact('holiday', 'branches'));
    }

    /**
     * Update the specified holiday in storage.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $data = $this->validatedData($request, $holiday->id);

        $holiday->update($data);

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Holiday updated successfully.');
    }

    /**
     * Remove the specified holiday from storage.
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Holiday deleted successfully.');
    }

    /**
     * Centralised validation logic.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|null                  $ignoreId  When updating, pass the model ID to ignore for uniqueness.
     * @return array
     */
    protected function validatedData(Request $request, int $ignoreId = null): array
    {
        $date = Carbon::parse($request->input('date'))->format('Y-m-d');

        return $request->validate([
            'title'       => ['required', 'string', 'max:100'],
            'date'        => [
                'required',
                'date',
                // Unique per date + branch combination
                Rule::unique('holidays')->where(function ($query) use ($request) {
                    return $query->where('branch_id', $request->input('branch_id'));
                })->ignore($ignoreId)
            ],
            'branch_id'   => ['nullable', 'exists:branches,id'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'date.unique' => 'A holiday already exists for this branch on the selected date.',
        ]);
    }
}
