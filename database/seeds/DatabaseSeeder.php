<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\User::create(['email' => 'support@digisave.co.ke', 'email_verified_at' => now(), 'name' => 'System Support', 'country_code' => 'KE', 'phone_number' => '0702123456', 'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi']);
    }
}
