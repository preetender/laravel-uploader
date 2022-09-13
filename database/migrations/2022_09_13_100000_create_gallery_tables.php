<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fat_galleries', function (Blueprint $table) {
            $table->id();
            $table->morphs('origin');
            $table->string('folder', 60)->index();
            $table->string('disk', 30)->default(Config::get('uploader.disk'));
            $table->timestamps();
        });

        Schema::create('fat_gallery_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('fat_galleries')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('filename', 60)->index();
            $table->string('extension', 4)->index();
            $table->integer('size');
            $table->integer('width');
            $table->integer('height');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fat_galleries');
        Schema::dropDatabaseIfExists('fat_gallery_files');
    }
};
