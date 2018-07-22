<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialAdminUser extends Seeder
{
    private $faker;

    public function __construct(\Faker\Generator $faker)
    {
        $this->faker = $faker;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (\App\User::admin()->exists()) {
            echo 'admin already exists, abort' . PHP_EOL;
            return;
        }

        $email = $this->faker->unique()->email;
        $password = $this->faker->password;

        echo 'E-Mail: ' . $email . PHP_EOL;
        echo 'Password: ' . $password . PHP_EOL;

        if (
            !\App\User::whereEmail($email)->admin()->exists()
        ) {
            \App\User::create(
                [
                    'type' => \App\User::TYPE_ADMIN,
                    'name' => 'Admin',
                    'email' => $email,
                    'password' => Hash::make($password),
                    'wallet_password' => Hash::make($password),
                    'frozen' => false,
                ]
            );
        }
    }
}
