<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductToSalesTable extends Migration
{
    
// testing productToSales relation table 

public function up()
{
    Schema::create('product_to_sales', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('product_id');
        $table->unsignedBigInteger('sales_id');
        $table->timestamps();
        
        // Define foreign key constraints
        $table->foreign('product_id')->references('id')->on('products')->name('product_to_sales_product_id_foreign');
        $table->foreign('sales_id')->references('id')->on('sales_details')->name('product_to_sales_sales_id_foreign');
    });
}

public function down()
{
    Schema::dropIfExists('product_to_sales');
}


};
