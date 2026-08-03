<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles
        $roles = [
            'admin'   => 'Administrador con acceso total al sistema',
            'gestor'  => 'Gestión de recursos y usuarios',
            'cliente' => 'Acceso cliente del sistema',
        ];

        foreach ($roles as $name => $description) {
            Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Crear usuario admin inicial
        $email    = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD', 'Admin1234!');

        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'name'     => 'Administrador',
                'password' => Hash::make($password),
                'active'   => true,
            ]
        );

        $admin->syncRoles(['admin']);

        $this->command->info('✅ Roles creados: admin, gestor, cliente');
        $this->command->info("✅ Usuario admin actualizado: {$email}");
        $this->command->warn('⚠️  Recuerda cambiar la contraseña tras el primer acceso.');
    }
}
