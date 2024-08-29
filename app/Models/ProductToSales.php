<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductToSales extends Model
{
    use HasFactory;

    protected $table = 'product_to_sales';

    protected $fillable = [
        'product_id',
        'sales_id',
    ];

}
