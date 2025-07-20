<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreateBill extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function orderBooker()
    {
        return $this->belongsTo(Salesman::class, 'order_booker_id');
    }

    public function salesman()
    {
        return $this->belongsTo(Salesman::class, 'salesman_id');
    }
}
