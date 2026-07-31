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
        Schema::create('course_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prerequisite_course_unit_id')->nullable()->constrained('course_units')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('title');
            $table->unsignedTinyInteger('lecture_hours')->default(0);
            $table->unsignedTinyInteger('practical_hours')->default(0);
            $table->unsignedTinyInteger('credit_units');
            $table->unsignedTinyInteger('contact_hours');
            $table->unsignedTinyInteger('year');
            $table->unsignedTinyInteger('semester');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_units');
    }
};
