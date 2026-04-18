<?php

namespace Database\Seeders;

/*
* `WithoutModelEvents` - used to disable model events during seeding, preventing any side effects that may occur when creating or updating models.
* `Seeder` class - base class for all seeders in Laravel, providing the structure and methods needed to seed the database.
* `DatabaseSeeder` class - seeding the application's database with initial data.
*/
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    // The `run` method is called when the seeder is executed, and it contains the logic for inserting data into the database.
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
