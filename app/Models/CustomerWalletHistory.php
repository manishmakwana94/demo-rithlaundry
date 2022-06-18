<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerWalletHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'customer_id', 'type','message','message_ar','amount','created_at','updated_at'
    ];
}
