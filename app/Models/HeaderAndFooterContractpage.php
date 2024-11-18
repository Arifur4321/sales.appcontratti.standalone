<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeaderAndFooterContractpage extends Model
{
    
    use HasFactory;

    // Define the table name
    protected $table = 'headerAndFooterTableContractpage';

    // Define fillable properties for mass assignment
    protected $fillable = [
        'contractID',
        'HeaderID',
        'HeaderPage',
        'FooterID',
        'FooterPage',


    ];
}

 