<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Creation du compte admin
        DB::table('admin_users')->where('id', '=', 1)->update([
            'email' => 'matnoire_email',
            'first_name' => 'Admin',
            'last_name' => 'Matnoire',
            'password' => Hash::make('matnoire44')
        ]);
        
        DB::table('model_has_roles')->insert([
            'role_id' => 1,
            'model_type' => 'Brackets\AdminAuth\Models\AdminUser',
            'model_id' => 2
        ]);
        
        factory(App\Models\User::class, 10)->create();
    }
}
