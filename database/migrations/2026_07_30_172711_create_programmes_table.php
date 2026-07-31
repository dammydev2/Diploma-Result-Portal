<?php

use App\Enums\ProgrammeLevel;
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
        Schema::create('programmes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('level', array_column(ProgrammeLevel::cases(), 'value'));
            $table->unsignedSmallInteger('min_credit_units')->nullable();
            $table->unsignedSmallInteger('max_credit_units')->nullable();
            $table->timestamps();

            $table->unique(['name', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programmes');
    }
};
