<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->text('bio')->nullable();
            $t->string('skills', 1000)->nullable();
            $t->string('location')->nullable();
            $t->string('company')->nullable();
            $t->string('avatar')->nullable();
        });
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->timestamps();
        });
        Schema::create('job_listings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('employer_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('category_id')->constrained()->restrictOnDelete();
            $t->string('title');
            $t->string('location');
            $t->string('type', 30);
            $t->unsignedInteger('salary_min');
            $t->unsignedInteger('salary_max');
            $t->text('description');
            $t->date('deadline');
            $t->string('status', 20)->default('published');
            $t->boolean('featured')->default(false);
            $t->timestamps();
            $t->index(['status', 'deadline']);
            $t->index(['category_id', 'type']);
        });
        Schema::create('applications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('job_listing_id')->constrained()->cascadeOnDelete();
            $t->string('status', 20)->default('applied');
            $t->timestamps();
            $t->unique(['user_id', 'job_listing_id']);
        });
        Schema::create('bookmarks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('job_listing_id')->constrained()->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['user_id', 'job_listing_id']);
        });
        Schema::create('notifications', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('type');
            $t->morphs('notifiable');
            $t->text('data');
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['notifications', 'bookmarks', 'applications', 'job_listings', 'categories'] as $name) {
            Schema::dropIfExists($name);
        }Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['bio', 'skills', 'location', 'company', 'avatar']));
    }
};
