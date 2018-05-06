<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RootUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\User::firstOrCreate(
            [
                'email' => 'root',
            ],
            [
                'name' => 'root',
                'password' => Hash::make('password'),
                'frozen' => false,
            ]
        );
    }
}
