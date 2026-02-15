<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        $this->call([
            StoreSeeder::class,
        ]);

        $token = $user->createToken('reviewer-token')->plainTextToken;

        $this->command->info('---------------------------------');
        $this->command->info('Laravel 12 API Ready!');
        $this->command->info('Admin Email: admin@example.com');
        $this->command->info('Reviewer Token: ' . $token);
        $this->command->info('---------------------------------');
    }
}
