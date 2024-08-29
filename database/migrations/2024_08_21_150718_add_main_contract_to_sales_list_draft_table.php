<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMainContractToSalesListDraftTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_list_draft', function (Blueprint $table) {
            $table->longText('main_contract')->nullable()->after('company_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_list_draft', function (Blueprint $table) {
            $table->dropColumn('main_contract');
        });
    }
}
