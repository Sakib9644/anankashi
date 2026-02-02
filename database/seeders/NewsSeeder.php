<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Str;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  public function run(): void
    {
        $faker = Faker::create();

        // Create 40 news items
        for ($i = 1; $i <= 40; $i++) {
            $newsId = DB::table('news')->insertGetId([
                'status' => $faker->randomElement(['publish', 'unpublish']),
                'slug' => Str::slug($faker->sentence(3) . '-' . $i),
                'thumbnail' => "news_thumbnail_$i.jpg", // or $faker->image(...)
                'title' => $faker->sentence(6),
                'short_description' => $faker->paragraph(2),
                'type' => $faker->word,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Each news has 2-3 details
            $detailsCount = rand(2, 3);
            for ($j = 1; $j <= $detailsCount; $j++) {
                $newsDetailId = DB::table('news_details')->insertGetId([
                    'news_id' => $newsId,
                    'title' => $faker->sentence(4),
                    'description' => $faker->paragraph(5),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Each detail has 2-4 images
                $imagesCount = rand(2, 4);
                for ($k = 1; $k <= $imagesCount; $k++) {
                    DB::table('news_details_images')->insert([
                        'news_details_id' => $newsDetailId,
                        'image' => "news_detail_{$newsDetailId}_image_$k.jpg",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('40 news items with details and images seeded successfully!');
    }
}
