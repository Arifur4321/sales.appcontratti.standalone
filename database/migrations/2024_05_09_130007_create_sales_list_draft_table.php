<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesListDraftTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_list_draft', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name')->nullable();
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->string('contract_name')->nullable();
            $table->unsignedBigInteger('variable_id')->nullable();
            $table->json('variable_json')->nullable();
            $table->unsignedBigInteger('price_id')->nullable();
            $table->json('price_json')->nullable();
            $table->string('selected_pdf_name')->nullable();
            $table->string('status')->nullable();
            $table->string('envelope_id')->nullable();
            $table->string('recipient_email')->nullable();
            $table->string('shareable_pdf_link')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            // Adding the new column for HTML content
            $table->longText('main_contract')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_list_draft');
    }
}
