<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCard extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'customer_id', 'card_token', 'last_four','is_default', 'created_at','updated_at'
    ];
}

