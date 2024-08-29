<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

 

class SalesListDraft extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sales_list_draft';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sales_id', 
        'product_id', 
        'product_name', 
        'contract_id', 
        'contract_name', 
        'variable_id', 
        'variable_json', 
        'price_id', 
        'price_json', 
        'selected_pdf_name', 
        'status', 
        'envelope_id',
        'recipient_email',
        'shareable_pdf_link',
        'company_id',
    ];

    

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'variable_json' => 'json',
        'price_json' => 'json',
    ];
}
