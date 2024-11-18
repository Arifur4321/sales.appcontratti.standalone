<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class createcontract extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content']; // Specify fillable fields

    // arifur can define relationships, accessors, mutators, etc. here
}