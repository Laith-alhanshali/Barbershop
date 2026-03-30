<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        // Create Restore and ForceDelete permissions for soft-deleted models
        $softDeletePermissions = [
            // Appointments
            'Restore:Appointment',
            'ForceDelete:Appointment',
            // Customers
            'Restore:Customer',
            'ForceDelete:Customer',
            // Invoices
            'ViewAny:Invoice',
            'View:Invoice',
            'Create:Invoice',
            'Update:Invoice',
            'Delete:Invoice',
            'Restore:Invoice',
            'ForceDelete:Invoice',
        ];

        foreach ($softDeletePermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => $guard,
            ]);
        }

        $super = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => $guard]);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $receptionist = Role::firstOrCreate(['name' => 'receptionist', 'guard_name' => $guard]);
        $barber = Role::firstOrCreate(['name' => 'barber', 'guard_name' => $guard]);

        $allPermissions = Permission::where('guard_name', $guard)->get();

        // super_admin و admin كل شيء
        $super->syncPermissions($allPermissions);
        $admin->syncPermissions($allPermissions);

        // receptionist: إدارة الحجوزات + العملاء + الفواتير + Restore (no ForceDelete)
        $receptionist->syncPermissions([
            // Appointments
            'ViewAny:Appointment',
            'View:Appointment',
            'Create:Appointment',
            'Update:Appointment',
            'Delete:Appointment',
            'Restore:Appointment',

            // Customers
            'ViewAny:Customer',
            'View:Customer',
            'Create:Customer',
            'Update:Customer',
            'Delete:Customer',
            'Restore:Customer',

            // Services (read-only)
            'ViewAny:Service',
            'View:Service',

            // Invoices (full CRUD + restore, no force delete)
            'ViewAny:Invoice',
            'View:Invoice',
            'Create:Invoice',
            'Update:Invoice',
            'Delete:Invoice',
            'Restore:Invoice',
        ]);

        // barber: عرض فقط للخدمات والحجوزات والفواتير
        $barber->syncPermissions([
            'ViewAny:Service',
            'View:Service',

            'ViewAny:Appointment',
            'View:Appointment',

            // Invoices (view only)
            'ViewAny:Invoice',
            'View:Invoice',
        ]);
    }
}
