<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contract_name',
        'product_id',
        'price_id',
        'editor_content',
        'logged_in_user_name',
        'image_url', // Add this line for the new column
    ];

    /**
     * Get the product associated with the contract.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function priceList()
    {
        return $this->belongsTo(PriceList::class, 'price_id');
    }
}
