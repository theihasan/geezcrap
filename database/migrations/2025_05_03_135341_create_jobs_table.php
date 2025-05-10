<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jobs_details', function (Blueprint $table) {
            $table->id();
            $table->string('employer_name');
            $table->string('employer_logo')->nullable();
            $table->string('employer_website')->nullable();
            $table->string('publisher');
            $table->string('employment_type')->nullable();
            $table->string('job_title');
            $table->foreignId('job_category_id')->nullable()->constrained('job_categories');
            $table->string('category_image')->nullable();
            $table->string('apply_link');
            $table->longText('description');
            $table->boolean('is_remote')->default(false);
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('google_link')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->float('min_salary')->nullable();
            $table->float('max_salary')->nullable();
            $table->string('salary_period')->nullable();
            $table->json('benefits')->nullable();
            $table->json('qualifications')->nullable();
            $table->json('responsibilities')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs_details');
    }
};
