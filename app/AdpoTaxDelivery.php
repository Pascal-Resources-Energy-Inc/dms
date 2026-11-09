<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdpoTaxDelivery extends Model
{
    protected $guarded = ['id'];
    protected $dates = ['sent_at', 'attempted_at'];
}
