<?php

use App\User;
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
        $users = [
            ['name' => 'PREI Super Admin', 'email' => 'admin@dms.test', 'role' => User::ROLE_SUPER_ADMIN, 'territory' => 'National Office'],
            ['name' => 'Provincial Distributor', 'email' => 'provincial@dms.test', 'role' => User::ROLE_PROVINCIAL_DISTRIBUTOR, 'territory' => 'Province Network'],
            ['name' => 'Area Distributor', 'email' => 'area@dms.test', 'role' => User::ROLE_AREA_DISTRIBUTOR, 'territory' => 'North Area'],
            ['name' => 'Mega Dealer', 'email' => 'mega@dms.test', 'role' => User::ROLE_MEGA_DEALER, 'territory' => 'Metro Cluster'],
            ['name' => 'Dealer', 'email' => 'dealer@dms.test', 'role' => User::ROLE_DEALER, 'territory' => 'Local Outlet'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                array_merge($user, [
                    'password' => Hash::make('password'),
                    'status' => 'active',
                ])
            );
        }
    }
}
