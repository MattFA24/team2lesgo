<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 public function up()
{
    Schema::create('saved_texts', function (Blueprint $table) {
        $table->id();
        $table->text('original_text');
        $table->text('generated_text');
        $table->string('type')->nullable(); // e.g., 'professional', 'casual'
        $table->timestamps();
    });
}

};
