<?php

namespace App\Http\Controllers;

use App\Area;
use App\AreaAd;
use App\Dealer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AreaController extends Controller
{
    public function index()
    {
        $areas = Area::query()
            ->with('geographicCoverages')
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
            'coverages' => ['required', 'array', 'min:1'],
            'coverages.*.region' => ['required', 'string', 'max:255'],
            'coverages.*.province' => ['required', 'string', 'max:255'],
            'coverages.*.city_municipality' => ['required', 'string', 'max:255'],
            'coverages.*.barangay' => ['required', 'string', 'max:255'],
        ]);

        $coverages = $this->normaliseCoverages($data['coverages']);

        if ($this->hasDuplicateCoverage($coverages)) {
            return back()->withInput()->withErrors(['coverages' => 'Each geographic coverage location must be unique.']);
        }

        DB::transaction(function () use ($data, $coverages) {
            $area = Area::create(['name' => trim($data['name'])]);
            $area->geographicCoverages()->createMany($coverages->all());
        });

        return redirect()
            ->route('areas')
            ->with('success', 'Area successfully added with ' . $coverages->count() . ' coverage location(s).');
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
            'coverages' => ['required', 'array', 'min:1'],
            'coverages.*.region' => ['required', 'string', 'max:255'],
            'coverages.*.province' => ['required', 'string', 'max:255'],
            'coverages.*.city_municipality' => ['required', 'string', 'max:255'],
            'coverages.*.barangay' => ['required', 'string', 'max:255'],
        ]);

        $oldName = $area->name;
        $newName = trim($data['name']);
        $coverages = $this->normaliseCoverages($data['coverages']);

        if ($this->hasDuplicateCoverage($coverages)) {
            return back()->withInput()->withErrors(['coverages' => 'Each geographic coverage location must be unique.']);
        }

        DB::transaction(function () use ($area, $newName, $oldName, $coverages) {
            $area->update(['name' => $newName]);
            $area->geographicCoverages()->delete();
            $area->geographicCoverages()->createMany($coverages->all());

            if ($oldName !== $newName) {
                AreaAd::where('area_name', $oldName)->update(['area_name' => $newName]);
                Dealer::where('area', $oldName)->update(['area' => $newName]);
            }
        });

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

    private function normaliseCoverages(array $coverages)
    {
        return collect($coverages)->map(function ($coverage) {
            return collect($coverage)->map(function ($value) {
                return trim($value);
            })->all();
        });
    }

    private function hasDuplicateCoverage($coverages)
    {
        return $coverages->count() !== $coverages->map(function ($coverage) {
            return strtolower(implode('|', [
                $coverage['region'],
                $coverage['province'],
                $coverage['city_municipality'],
                $coverage['barangay'],
            ]));
        })->unique()->count();
    }
}
