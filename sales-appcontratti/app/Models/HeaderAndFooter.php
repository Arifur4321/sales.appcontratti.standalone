<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeaderAndFooter extends Model
{
    use HasFactory;
    protected $primaryKey = 'id'; // Specify the primary key

    protected $table = 'header_and_footer';

    protected $fillable = [
        'name',
        'type',
        'editor_content',
    ];
}
