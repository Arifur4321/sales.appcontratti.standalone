<?php
 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePriceListsTable extends Migration
{
    public function up()
    {
        Schema::create('price_lists', function (Blueprint $table) {
            $table->id();
            $table->string('pricename');
            $table->string('currency')->nullable();
            $table->integer('fixedvalue')->nullable();
            $table->integer('dynamicminRange')->nullable();
            $table->integer('dynamicmaxRange')->nullable();
            $table->string('enableVat')->nullable();
            $table->integer('vatPercentage')->nullable();
            $table->string('price')->nullable();
            $table->string('external')->nullable();
            $table->string('selectPriceType')->nullable();
            $table->string('singlePayment')->nullable();
            $table->string('multiplePayments')->nullable();
            $table->string('EditableDates')->nullable();
            $table->string('frequency')->nullable();
            $table->integer('paymentMinRange')->nullable();
            $table->integer('paymentMaxRange')->nullable();
            $table->string('paymentExampleText')->nullable();
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
        Schema::dropIfExists('price_lists');
    }
}
