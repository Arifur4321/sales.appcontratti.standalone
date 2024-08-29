<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SalesDetails; 
use Illuminate\Support\Facades\Hash;

class CreateSalesDetailsTable extends Migration
{
    

    public function up()
    {
        Schema::create('sales_details', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname');
            $table->string('nickname')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->text('description')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        SalesDetails::create([
            'name' => 'sales',
            'surname' => 'person', // Replace '...' with the appropriate surname value
            'nickname' => null, // Assuming nickname is nullable
            'phone' => null, // Assuming phone is nullable
            'email' => 'hatbazar627@gmail.com',
            'email_verified_at' => '2022-01-02 17:04:58',
            'password' => Hash::make('12345678'), // Hashing the password
            'description' => null, // Assuming description is nullable
            // Assuming you don't have avatar and dob columns in sales_details table
            
            'created_at' => now(),
            'updated_at' => now(), // Assuming you have updated_at column in the sales_details table
        ]);
        

    }

    public function down()
    {
        Schema::dropIfExists('sales_details');
    }


};
