<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Customer extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $guarded = [];

    public function orderBookers()
    {
        return $this->belongsToMany(Salesman::class, 'customer_orderbooker', 'customer_id', 'salesman_id');
    }

    public function getOrderBookerNamesAttribute()
    {
        // Decode JSON or fallback
        $ids = json_decode($this->order_booker_id, true);

        // Ensure it's always an array
        if (is_null($ids)) {
            $ids = []; // empty
        } elseif (is_int($ids)) {
            $ids = [$ids]; // single ID case
        }

        return \App\Models\Salesman::whereIn('id', $ids)->pluck('name')->toArray();
    }


    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function businessType()
    {
        return $this->belongsTo(BusinessType::class);
    }

    public function localSales()
    {
        return $this->hasMany(LocalSale::class, 'customer_id');
    }
}
