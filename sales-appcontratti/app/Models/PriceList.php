<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceList extends Model
{
    use HasFactory;
 
    protected $table = 'price_lists';

    protected $fillable = [
        'pricename',
        'currency', 
        'fixedvalue',
        'dynamicminRange',
        'dynamicmaxRange', 
        'enableVat',
        'vatPercentage',
        'price',
        'external', 
        'selectPriceType', 
        'singlePayment', 
        'multiplePayments', 
        'EditableDates',
        'frequency',
        'paymentMinRange', 
        'paymentMaxRange', 
        'paymentExampleText',
    ];
}