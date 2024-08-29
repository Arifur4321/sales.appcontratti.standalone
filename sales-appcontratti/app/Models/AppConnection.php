<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppConnection extends Model
{
    use HasFactory;

    protected $table = 'app_connection';

    protected $fillable = [
        'company_id',
        'type',
        'api_key',
    ];
}
