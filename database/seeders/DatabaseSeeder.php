<?php

namespace Database\Seeders;

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
        cache()->forget('top_users_data_redis');
        cache()->forget('top_users_data');

        \App\Models\User::factory(500)->create();
        \App\Models\Post::factory(10000)->create();
    }
}
