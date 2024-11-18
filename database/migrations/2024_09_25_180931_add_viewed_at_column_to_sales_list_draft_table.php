<?php
 

 use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddViewedAtColumnToSalesListDraftTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_list_draft', function (Blueprint $table) {
            // Add a JSON column to store viewedAt and viewedIp as JSON data
            $table->json('viewedAt')->nullable();
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
            $table->dropColumn('viewedAt');
        });
    }
}
