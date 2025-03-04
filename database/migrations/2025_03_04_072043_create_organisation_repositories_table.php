<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('organisation_repositories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description');
            $table->string('url')->nullable();
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->string('language')->nullable();
            $table->string('license')->nullable();
            $table->integer('forks_count')->default(0);
            $table->integer('open_issues_count')->default(0);
            $table->integer('watchers_count')->default(0);
            $table->string('default_branch')->default('main');
            $table->json('topics')->nullable();
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
        Schema::dropIfExists('organisation_repositories');
    }
};
