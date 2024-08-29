<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('contracts')) {
            Schema::create('contracts', function (Blueprint $table) {
                $table->id();
                $table->string('contract_name');
                $table->unsignedBigInteger('product_id')->nullable();
                //$table->foreign('product_id')->references('id')->on('products');
                $table->foreign('product_id')->references('id')->on('products')->name('contracts_product_id_foreign');
                $table->unsignedBigInteger('price_id')->nullable();
                $table->foreign('price_id')->references('id')->on('price_lists')->name('contracts_price_id_foreign');
            
                $table->longText('editor_content')->nullable();
                $table->text('logged_in_user_name')->nullable();
                $table->string('image_url')->nullable();
                $table->timestamps();
            });
        }
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contracts');
    }
}
