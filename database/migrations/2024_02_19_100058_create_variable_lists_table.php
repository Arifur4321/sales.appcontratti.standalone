<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVariableListsTable extends Migration
{
    public function up()
    {
        Schema::create('variable_lists', function (Blueprint $table) {
            $table->bigIncrements('VariableID');
            $table->string('VariableName');
            $table->string('VariableType');
         //   $table->string('VariableLabelValue')->nullable();
            $table->json('VariableLabelValue')->nullable();
  
            $table->text('Description');
           // $table->timestamp('CreatedDate')->useCurrent();
            //$table->timestamp('UpdatedDate')->nullable()->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('variable_lists');
    }
}


