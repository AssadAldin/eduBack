<?php

use Database\Seeders\FooterSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('footers', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            $table->text('ar_description');
            $table->string('email');
            $table->string('phone');
            $table->timestamps();
        });

        // Run the footer seeder after the table is created
        (new FooterSeeder)->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('footers');
    }
};
