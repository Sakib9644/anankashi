<?php

namespace Database\Seeders;

use App\Models\Property;
use Attribute;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(BrandSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(SubcategorySeeder::class);
        $this->call(NotificationSeeder::class);
        $this->call(PageSeeder::class);
        $this->call(TransactionSeeder::class);
        $this->call(MenuSeeder::class);
        $this->call(AttributeSeeder::class);
        $this->call(PropertySeeder::class);
        $this->call(TemplateSeeder::class);
        $this->call(NewsSeeder::class);
    }
}
