<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    public function getServiceIdAttribute($value)
    {
        return explode(',', $value);
    }

    public function setServiceIdAttribute($value)
    {
        $this->attributes['service_id'] = implode(',', $value);
    }
}

