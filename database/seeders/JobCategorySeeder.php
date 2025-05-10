<?php

namespace Database\Seeders;

use App\Models\JobCategory;
use Illuminate\Database\Seeder;

class JobCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Software Development',
                'slug' => 'software-development',
                'image_path' => '/images/categories/software-development.jpg',
                'description' => 'Jobs related to software development and programming'
            ],
            [
                'name' => 'Data Science',
                'slug' => 'data-science',
                'image_path' => '/images/categories/data-science.jpg',
                'description' => 'Jobs related to data analysis, machine learning, and AI'
            ],
            [
                'name' => 'Marketing',
                'slug' => 'marketing',
                'image_path' => '/images/categories/marketing.jpg',
                'description' => 'Jobs related to digital marketing, SEO, and content creation'
            ],
            [
                'name' => 'Design',
                'slug' => 'design',
                'image_path' => '/images/categories/design.jpg',
                'description' => 'Jobs related to UI/UX design, graphic design, and product design'
            ],
            [
                'name' => 'Customer Service',
                'slug' => 'customer-service',
                'image_path' => '/images/categories/customer-service.jpg',
                'description' => 'Jobs related to customer support and service'
            ],
            [
                'name' => 'Other',
                'slug' => 'other',
                'image_path' => '/images/categories/other.jpg',
                'description' => 'Other job categories'
            ],
        ];

        foreach ($categories as $category) {
            JobCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
