<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'full_name' => 'Rehan EO',
            'email' => 'rehan@rehan.com',
            'role' => 'eo',
            'phone' => '082143608440',
            'password' => md5('rehan'),
        ]);

        User::create([
            'full_name' => 'Rehan Guitaris',
            'email' => 'rehang@gmail.com',
            'role' => 'musician',
            'phone' => '082143608440',
            'password' => md5('rehang'),
        ]);
    }
}
