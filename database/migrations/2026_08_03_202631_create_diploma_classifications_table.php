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
        Schema::create('diploma_classifications', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_cgpa', 3, 2);
            $table->decimal('max_cgpa', 3, 2);
            $table->string('classification')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diploma_classifications');
    }
};
