<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('dictionaries', function (Blueprint $table) {
            $table->id();
            $table->string('word')->unique(); // Each word should be unique
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('dictionaries');
    }
};
