<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryBoy extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'delivery_boy_name', 'phone_number','email','password','profile_picture','status','otp','fcm_token','phone_with_code'
    ];
}
