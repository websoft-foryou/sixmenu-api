<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('users')->where('email', 'admin@atarit.com')->delete();
        \DB::table('users')->insert(['name'=>'admin', 'email'=> 'admin@atarit.com', 'password'=>'$2y$10$I8PfUEw.jgcDfl2nMdgpQe7fDCv.wonIQnNG/qkBHkU3ADe9do9.6',
            'email_verified_at'=>date('Y-m-d H:i:s'), 'membership' => '101', 'membership_created_at'=>date('Y-m-d H:i:s'),
            'created_at'=>date('Y-m-d H:i:s'), 'updated_at'=>date('Y-m-d H:i:s')]);
    }
}
