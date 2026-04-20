<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomDeduction extends Model
{
    protected $fillable = ['name', 'type', 'amount', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
