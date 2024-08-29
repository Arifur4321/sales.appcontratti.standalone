<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 

class contractvariablecheckbox extends Model
{
    use HasFactory;

    protected $table = 'contractvariablecheckbox';
    
    protected $fillable =
     ['ContractID',
      'VariableID',
      'Mandatory',
      'Order'];
}
