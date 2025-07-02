<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // Add this inside the Product class
    protected $fillable = ['name', 'description', 'price'];
}
