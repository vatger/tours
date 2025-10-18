<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $u = new User;
        $u->firstname = 'John';
        $u->lastname = 'Doe';
        $u->id = 1450775;
        $u->save();
    }
}
