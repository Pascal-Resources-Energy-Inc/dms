<?php

namespace App\Support;

use App\User;

class DistributorPricing
{
    public static function usesVisminPrice(User $user): bool
    {
        if (!$user->hasAreaDistributorAccess()) {
            return false;
        }

        // PD profiles are stored separately; only older accounts use an AD profile.
        $profile = $user->role === User::ROLE_PROVINCIAL_DISTRIBUTOR
            ? ($user->provincialDistributor ?: $user->ad)
            : $user->ad;
        $region = trim((string) optional($profile)->location_region);
        if ($region === '') {
            $region = trim((string) $user->region);
        }

        // Use the saved region, never an editable delivery address or submitted price.
        // Region names are stored by the distributor address selectors.
        if (preg_match('/\b(visayas|mindanao|caraga|soccsk[s]?argen|barmm|armm|nir|negros island region|zamboanga peninsula|davao region)\b/i', $region)) {
            return true;
        }

        // Support abbreviated region labels without confusing Region V with VI–VIII.
        return preg_match('/^(?:region[\s-]*)?(?:VI|VII|VIII|IX|X|XI|XII|XIII)(?:\b|$)/i', $region) === 1
            || preg_match('/^region[\s-]*(?:0?[6-9]|1[0-3])\b/i', $region) === 1;
    }
}
