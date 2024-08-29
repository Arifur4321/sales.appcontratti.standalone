<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contractvariablecheckbox', function (Blueprint $table) {
            $table->id();
            $table->string('LoggedinUser');
            $table->string('ContractID');
            $table->string('VariableID');
            $table->string('Mandatory')->nullable();
            $table->unsignedInteger('Order')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractvariablecheckbox');
    }
};



 
