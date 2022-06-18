<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentResponse extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'customer_id', 'order_id','payment_mode','payment_response'
    ];
}

