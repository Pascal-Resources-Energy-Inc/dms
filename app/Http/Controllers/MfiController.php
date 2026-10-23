<?php

namespace App\Http\Controllers;

use App\Mfi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class MfiController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $mfis = Mfi::query()->orderBy('name')->get()->map(function (Mfi $mfi) {
            $mfi->centers_count = $this->centerCount($mfi->name);
            $mfi->users_count = $this->userCount($mfi->name);

            return $mfi;
        });

        return view('mfis.index', compact('mfis'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $this->validatedData($request);
        Mfi::create($data);

        return redirect()->route('mfis.index')->with('success', 'MFI successfully added.');
    }

    public function update(Request $request, Mfi $mfi)
    {
        $this->authorizeAdmin($request);

        $data = $this->validatedData($request, $mfi);
        $oldName = $mfi->name;
        $newName = $data['name'];
        $showCustomerFullName = $data['show_customer_full_name'];

        DB::transaction(function () use ($mfi, $oldName, $newName, $showCustomerFullName) {
            $mfi->update([
                'name' => $newName,
                'show_customer_full_name' => $showCustomerFullName,
            ]);

            if ($oldName !== $newName) {
                if (Schema::hasTable('centers') && Schema::hasColumn('centers', 'mfi')) {
                    DB::table('centers')->where('mfi', $oldName)->update(['mfi' => $newName]);
                }

                if (Schema::hasColumn('users', 'mfi_type')) {
                    DB::table('users')->where('mfi_type', $oldName)->update(['mfi_type' => $newName]);
                }
            }
        });

        return redirect()->route('mfis.index')->with('success', 'MFI successfully updated.');
    }

    public function destroy(Request $request, Mfi $mfi)
    {
        $this->authorizeAdmin($request);

        if ($this->centerCount($mfi->name) || $this->userCount($mfi->name)) {
            return redirect()->route('mfis.index')
                ->with('error', 'This MFI is assigned to centers or user accounts and cannot be deleted.');
        }

        $mfi->delete();

        return redirect()->route('mfis.index')->with('success', 'MFI successfully deleted.');
    }

    private function validatedData(Request $request, Mfi $mfi = null)
    {
        $request->merge(['name' => strtoupper(trim((string) $request->input('name')))]);

        $unique = Rule::unique('mfis', 'name');
        if ($mfi) {
            $unique->ignore($mfi->id);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', $unique],
            'show_customer_full_name' => ['required', Rule::in(['0', '1', 0, 1])],
        ], [
            'show_customer_full_name.required' => 'Choose whether this MFI can see customers’ full names.',
            'show_customer_full_name.in' => 'Select Yes or No for customer full-name visibility.',
        ]);

        $data['show_customer_full_name'] = (bool) ((int) $data['show_customer_full_name']);

        return $data;
    }

    private function authorizeAdmin(Request $request)
    {
        abort_unless(optional($request->user())->role === 'Admin', 403, 'Only administrators can manage MFIs.');
    }

    private function centerCount($name)
    {
        if (! Schema::hasTable('centers') || ! Schema::hasColumn('centers', 'mfi')) {
            return 0;
        }

        return DB::table('centers')->where('mfi', $name)->count();
    }

    private function userCount($name)
    {
        if (! Schema::hasColumn('users', 'mfi_type')) {
            return 0;
        }

        return DB::table('users')->where('mfi_type', $name)->count();
    }
}
