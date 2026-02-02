<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RollbackNewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  public function run(): void
    {
        // Delete seeded data
        DB::table('news_details_images')->truncate();
        DB::table('news_details')->truncate();
        DB::table('news')->truncate();
    }
}
