<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtpviewedToSalesListDraftTable extends Migration
{
     
    public function up()
    {
        Schema::table('sales_list_draft', function (Blueprint $table) {
            $table->json('OTPviewed')->nullable()->after('viewedAt'); // Add the new OTPviewed column
        });
    }

    
    public function down()
    {
        Schema::table('sales_list_draft', function (Blueprint $table) {
            $table->dropColumn('OTPviewed'); // Drop the column if rolling back
        });
    }
}
