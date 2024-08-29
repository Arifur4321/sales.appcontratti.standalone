<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


use Illuminate\Contracts\Auth\MustVerifyEmail;
 
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class SalesDetails extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $table = 'sales_details'; // Set the table name

    protected $fillable = ['name', 'surname', 'nickname' ,'phone','email', 'password','description']; // Define fillable fields

      
    protected $hidden = [
        
        'remember_token',
    ];

    
    
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}

 