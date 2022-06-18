<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id', 'address_id','pickup_date','pickup_time', 'delivery_date','delivery_time','total','discount','sub_total','delivery_cost','promo_id','delivered_by','payment_mode','order_id','status','items','created_at','updated_at'
    ];
}

