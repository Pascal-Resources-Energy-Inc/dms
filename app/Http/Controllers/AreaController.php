<?php

namespace App\Http\Controllers;

use App\Area;
use App\AreaAd;
use App\Dealer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AreaController extends Controller
{
    public function index()
    {
        $areas = Area::query()
            ->withCount([
                'assignedAreas as assigned_distributors_count',
            ])
            ->orderBy('name')
            ->get();

        return view('areas.index', compact('areas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('areas', 'name')],
        ]);

        Area::create([
            'name' => trim($data['name']),
        ]);

        return redirect()
            ->route('areas')
            ->with('success', 'Area successfully added.');
    }

    public function update(Request $request, Area $area)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('areas', 'name')->ignore($area->id),
            ],
        ]);

        $oldName = $area->name;
        $newName = trim($data['name']);

        $area->update([
            'name' => $newName,
        ]);

        if ($oldName !== $newName) {
            AreaAd::where('area_name', $oldName)->update(['area_name' => $newName]);
            Dealer::where('area', $oldName)->update(['area' => $newName]);
        }

        return redirect()
            ->route('areas')
            ->with('success', 'Area successfully updated.');
    }

    public function destroy(Area $area)
    {
        $assignedDistributors = AreaAd::where('area_name', $area->name)->count();
        $assignedDealers = Dealer::where('area', $area->name)->count();

        if ($assignedDistributors || $assignedDealers) {
            return redirect()
                ->route('areas')
                ->with('error', 'This area is already assigned and cannot be deleted.');
        }

        $area->delete();

        return redirect()
            ->route('areas')
            ->with('success', 'Area successfully deleted.');
    }
}
