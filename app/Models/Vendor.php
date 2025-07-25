<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];


    public function builties()
    {
        return $this->hasMany(VendorBuilty::class, 'vendor_id');
    }
}
