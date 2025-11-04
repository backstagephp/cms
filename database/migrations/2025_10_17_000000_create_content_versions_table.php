<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_versions', function (Blueprint $table) {
            $table->id();
            $table->string('content_ulid');
            $table->json('data');
            $table->timestamps();

            $table->foreign('content_ulid')
                ->references('ulid')
                ->on('content')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_versions');
    }
};
