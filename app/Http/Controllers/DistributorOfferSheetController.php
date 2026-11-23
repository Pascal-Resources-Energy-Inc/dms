<?php

namespace App\Http\Controllers;

use App\AreaDistributor;
use App\ProvincialDistributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DistributorOfferSheetController extends Controller
{
    public function area(Request $request, $id, $area)
    {
        return $this->download($request, AreaDistributor::findOrFail($id), $area);
    }

    public function provincial(Request $request, $id, $area)
    {
        return $this->download($request, ProvincialDistributor::findOrFail($id), $area);
    }

    private function download(Request $request, $profile, $areaId)
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['Admin', 'MFI'], true) || (int) $profile->user_id === (int) $user->id, 403);
        $area = $profile->areas()->findOrFail($areaId);
        $path = $area->offer_sheet_path;
        abort_unless($path && strpos($path, 'distributor-offer-sheets/') === 0 && strpos($path, '..') === false, 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, $area->offer_sheet_name ?: basename($path));
    }
}
