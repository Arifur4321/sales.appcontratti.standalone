<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Template extends Model

{
        use HasFactory;

       
        protected $fillable = [
                'email_content',
                'sms_content',
                'compane_email',
                'company_id',
                'email_subject', // New field
                'watermark',     // New field
       ];


}
