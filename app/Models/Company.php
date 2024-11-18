<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['company_name', 'address', 'vat_number' , 'NumOfsales' ];

      // A company can have many users
      public function users()
      {
          return $this->hasMany(User::class);
      }
}
