<?php

namespace Database\Seeders;

use App\Models\Footer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FooterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Footer::firstOrCreate([], [
            'description' => 'Your default footer description.',
            'ar_description' => 'وصف افتراضيي الخاص بك.',
            'email' => 'info@example.com',
            'phone' => '+123456789',
        ]);
    }
}
