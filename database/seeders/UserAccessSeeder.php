<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\User;
use App\Models\UserOutlet;
use Illuminate\Database\Seeder;

class UserAccessSeeder extends Seeder
{
    public function run(): void
    {
        $users = collect([
            [
                'name' => 'Raka Pradipta',
                'email' => 'owner@bersihlaundry.test',
                'phone' => '0812-8471-9203',
                'global_role' => 'owner',
            ],
            [
                'name' => 'Maya Wulandari',
                'email' => 'admin@bersihlaundry.test',
                'phone' => '0813-4029-7718',
                'global_role' => 'admin',
            ],
            [
                'name' => 'Nadia Paramita',
                'email' => 'cashier.sby@bersihlaundry.test',
                'phone' => '0821-6347-1902',
                'global_role' => 'staff',
            ],
            [
                'name' => 'Farhan Mahendra',
                'email' => 'staff.sby@bersihlaundry.test',
                'phone' => '0857-1928-4406',
                'global_role' => 'staff',
            ],
            [
                'name' => 'Dimas Kartawijaya',
                'email' => 'cashier.sda@bersihlaundry.test',
                'phone' => '0822-4938-1507',
                'global_role' => 'staff',
            ],
        ])->map(fn (array $user) => User::updateOrCreate(
            ['email' => $user['email']],
            [
                ...$user,
                'password' => 'password',
                'is_active' => true,
            ],
        ));

        $outlets = Outlet::query()->get()->keyBy('code');

        $this->grantAllOutlets($users[0], 'owner', $outlets);
        $this->grantAllOutlets($users[1], 'admin', $outlets);

        $this->grantOutlet($users[2], $outlets['SBY'], 'cashier', [
            'can_manage_orders' => true,
            'can_manage_payments' => true,
        ], true);

        $this->grantOutlet($users[3], $outlets['SBY'], 'staff', [
            'can_manage_orders' => true,
            'can_manage_payments' => false,
        ], true);

        $this->grantOutlet($users[4], $outlets['SDA'], 'cashier', [
            'can_manage_orders' => true,
            'can_manage_payments' => true,
        ], true);
    }

    private function grantAllOutlets(User $user, string $role, iterable $outlets): void
    {
        foreach ($outlets as $outlet) {
            $this->grantOutlet($user, $outlet, $role, [
                'can_manage_orders' => true,
                'can_manage_payments' => true,
                'can_manage_services' => true,
                'can_manage_reports' => true,
                'can_manage_users' => true,
                'can_manage_settings' => true,
            ], (bool) $outlet->is_main);
        }
    }

    private function grantOutlet(User $user, Outlet $outlet, string $role, array $permissions, bool $isPrimary = false): void
    {
        UserOutlet::updateOrCreate(
            [
                'user_id' => $user->id,
                'outlet_id' => $outlet->id,
            ],
            [
                'role' => $role,
                'can_manage_orders' => $permissions['can_manage_orders'] ?? false,
                'can_manage_payments' => $permissions['can_manage_payments'] ?? false,
                'can_manage_services' => $permissions['can_manage_services'] ?? false,
                'can_manage_reports' => $permissions['can_manage_reports'] ?? false,
                'can_manage_users' => $permissions['can_manage_users'] ?? false,
                'can_manage_settings' => $permissions['can_manage_settings'] ?? false,
                'is_primary' => $isPrimary,
                'is_active' => true,
            ],
        );
    }
}
