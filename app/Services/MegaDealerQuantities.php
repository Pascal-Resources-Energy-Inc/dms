<?php

namespace App\Services;

class MegaDealerQuantities extends DealerQuantities
{
    public function addTo($query, $connection, $profileTable, $profileAlias, $dedicated = true)
    {
        return parent::addTo($query, $connection, $profileTable, $profileAlias, $dedicated);
    }
}
