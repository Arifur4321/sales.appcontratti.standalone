<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeaderAndFooterTable extends Migration
{
    public function up()
    {
        Schema::create('header_and_footer', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->text('editor_content');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('header_and_footer');
    }
}

 

