<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mfi extends Model
{
    protected $fillable = ['name', 'show_customer_full_name'];

    protected $casts = [
        'show_customer_full_name' => 'boolean',
    ];
}
