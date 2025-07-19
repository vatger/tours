<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Services\TranslatorGoogle;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info("==>> Iniciado Seeder de users");
        $motives = [];
        $this->command->info("====>> Iniciado translator de motivos");

        $translateds = TranslatorGoogle::translate('Cadastro inicial');
        $motives = $translateds['translations'];
        $this->command->info("====>> Encerrado translator de motivos");

        // Limpa a pasta de avatares (opcional)
        Storage::deleteDirectory('image/user_avatar');
        Storage::makeDirectory('image/user_avatar');
        $systemRole = Role::firstOrCreate(['name' => 'system-admin']);
        // Usuário system padrão
        $status = '2'; // 2 = aprovado
        $isActive = true; // Ativo
        $userSystem = User::create([
            'name' => 'Admin System',
            'email' => 'adminsystem@example.com',
            'password' => Hash::make('password'),
            'avatar' => $this->storeAvatar('Admin'),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
            'remember_token' => Str::random(10),
            'is_active' => $isActive,
            'approved_status' => $status
        ])->assignRole([
            $systemRole,
        ]);

        $userSystem->approvedMotives()->create([
            'approved_status' => $status,
            'causer_type' => User::class, // Tipo do usuário que ativou
            'causer_id' => $userSystem->id, // ID do usuário que ativou
            'motive' => $motives,
            // 'motive' => $motive,
        ]);
        $userSystem->activeMotives()->create([
            'is_active' => $isActive,
            'causer_type' => User::class, // Tipo do usuário que ativou
            'causer_id' => $userSystem->id, // ID do usuário que ativou
            'motive' => $motives,
            // 'motive' => $motive,
        ]);
        $this->command->info("====>> Atribuída role 'system-admin' para {$userSystem->email}");

        // Usuário super admin padrão
        $adminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $user = User::create([
            'name' => 'Super Administrador',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'avatar' => $this->storeAvatar('Super'),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
            'remember_token' => Str::random(10),
            'is_active' => true,
            'approved_status' => '2', // 2 = aprovado
        ])->assignRole([
            $adminRole,
        ]);
        $user->approvedMotives()->create([
            'approved_status' => $status,
            'causer_type' => User::class, // Tipo do usuário que ativou
            'causer_id' => $userSystem->id, // ID do usuário que ativou
            'motive' => $motives,
            // 'motive' => $motive,
        ]);
        $user->activeMotives()->create([
            'is_active' => $isActive,
            'causer_type' => User::class, // Tipo do usuário que ativou
            'causer_id' => $userSystem->id, // ID do usuário que ativou
            'motive' => $motives,
            // 'motive' => $motive,
        ]);
        $this->command->info("====>> Atribuída role 'admin' para {$user->email}");


        $managerRole = Role::firstOrCreate(['name' => 'users-manager']);
        // Usuário users manager padrão
        $user = User::create([
            'name' => 'Users Manager',
            'email' => 'usersmanager@example.com',
            'password' => Hash::make('password'),
            'avatar' => $this->storeAvatar('Users'),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
            'remember_token' => Str::random(10),
            'is_active' => true,
            'approved_status' => '2', // 2 = aprovado
        ])->assignRole([
            $managerRole,
        ]);
        $user->approvedMotives()->create([
            'approved_status' => $status,
            'causer_type' => User::class, // Tipo do usuário que ativou
            'causer_id' => $userSystem->id, // ID do usuário que ativou
            'motive' => $motives,
            // 'motive' => $motive,
        ]);
        $user->activeMotives()->create([
            'is_active' => $isActive,
            'causer_type' => User::class, // Tipo do usuário que ativou
            'causer_id' => $userSystem->id, // ID do usuário que ativou
            'motive' => $motives,
            // 'motive' => $motive,
        ]);
        $this->command->info("====>> Atribuída role 'manager Role' para {$user->email}");

        // Usuário guest padrão
        $guestRole = Role::firstOrCreate(['name' => 'guests']);


        // Usuários de exemplo
        $usersExample = [
            [
                'name' => 'João Silva',
                'email' => 'joao@example.com',
                'password' => Hash::make('password'),
                'avatar' => $this->storeAvatar('Joao'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
                'remember_token' => Str::random(10),
                'is_active' => true,
                'approved_status' => '2', // 2 = aprovado

            ],
            [
                'name' => 'Maria Souza',
                'email' => 'maria@example.com',
                'password' => Hash::make('password'),
                'avatar' => $this->storeAvatar('Maria'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
                'remember_token' => Str::random(10),
                'is_active' => true,
                'approved_status' => '2', // 2 = aprovado

            ],
            [
                'name' => 'Carlos Oliveira',
                'email' => 'carlos@example.com',
                'password' => Hash::make('password'),
                'avatar' => $this->storeAvatar('Carlos'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
                'remember_token' => Str::random(10),
                'is_active' => true,
                'approved_status' => '2', // 2 = aprovado

            ],
            [
                'name' => 'Ana Santos',
                'email' => 'ana@example.com',
                'password' => Hash::make('password'),
                'avatar' => $this->storeAvatar('Ana'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
                'remember_token' => Str::random(10),
                'is_active' => true,
                'approved_status' => '2', // 2 = aprovado

            ],
        ];
        foreach ($usersExample as $userData) {
            $user = User::create($userData);
            $user->assignRole($managerRole);
            $user->approvedMotives()->create([
                'approved_status' => $status,
                'causer_type' => User::class, // Tipo do usuário que ativou
                'causer_id' => $userSystem->id, // ID do usuário que ativou
                'motive' => $motives,
                // 'motive' => $motive,
            ]);
            $user->activeMotives()->create([
                'is_active' => $isActive,
                'causer_type' => User::class, // Tipo do usuário que ativou
                'causer_id' => $userSystem->id, // ID do usuário que ativou
                'motive' => $motives,
                // 'motive' => $motive,
            ]);
            $this->command->info("====>> Atribuída role 'manager' para {$user->email}");
        }

        $this->command->info("====>> Iniciado Seeder de 200 usuários 'guests' ");

        $this->storeDefaultAvatar('Guests');
        // 200 usuários fictícios (opcional)
        $user = User::factory()->count(200)->create();
        $this->command->info("====>> Encerrado Seeder de 200 usuários 'guests' ");
        $this->command->info("====>> Atribuir 'guests' para usuários sem roles");

        $usersWithoutRoles = User::whereDoesntHave('roles')->get();
        if ($usersWithoutRoles->isEmpty()) {
            $this->command->info('======>> Não há usuários sem roles para atribuir.');
            return;
        }
        foreach ($usersWithoutRoles as $user) {

            $user->assignRole($guestRole);
            $user->approvedMotives()->create([
                'approved_status' => $status,
                'causer_type' => User::class, // Tipo do usuário que ativou
                'causer_id' => $userSystem->id, // ID do usuário que ativou
                'motive' => $motives,
                // 'motive' => $motive,
            ]);
            $user->activeMotives()->create([
                'is_active' => $isActive,
                'causer_type' => User::class, // Tipo do usuário que ativou
                'causer_id' => $userSystem->id, // ID do usuário que ativou
                'motive' => $motives,
                // 'motive' => $motive,
            ]);
            $this->command->info("======>> Atribuída role 'guests' para {$user->email}");
        }

        $this->command->info("====>> Iniciado Seeder de ajuste de activity logs causer");

        $activities = ActivityLog::whereNull('causer_type')->get();
        // Usuário system padrão
        $causer_type = User::class;
        $causer_id = $userSystem->id;

        foreach ($activities as $activity) {
            $activity->update(['causer_type' => $causer_type, 'causer_id' => $causer_id]);
            $this->command->info("======>> Atribuído causer para {$activity->id}");
        }
        $this->command->info("====>> Encerrado Seeder de ajuste de activity logs causer");
        $this->command->info("==>> Encerrado Seeder de users");
    }



    /**
     * Gera um avatar fictício usando a API DiceBear
     */
    private function storeAvatar(string $seed): string
    {
        $url = "https://api.dicebear.com/7.x/initials/svg?seed={$seed}&scale=80";
        $contents = file_get_contents($url);
        // $path = "avatars/{$seed}-" . time() . ".svg"; // Nome único
        $storageAvatar = User::AVATAR_STORAGE;
        $savedNameAvatar = '';
        $originalFileName = '';
        $fileSize = 0;
        $fileExtension = '';
        $uploadData = [];
        $storageDisk = 'public';
        $fileName = $seed . '-' . time() . '.svg'; // Nome único com timestamp

        Storage::put($storageAvatar . DIRECTORY_SEPARATOR . $fileName, $contents, $storageDisk);
        return $fileName; // Retorna o caminho relativo
    }


    /**
     * Gera um avatar fictício usando a API DiceBear
     */
    private function storeDefaultAvatar(string $seed): string
    {
        $url = "https://api.dicebear.com/7.x/initials/svg?seed={$seed}&scale=80";
        $contents = file_get_contents($url);
        // $path = "avatars/{$seed}-" . time() . ".svg"; // Nome único
        $storageAvatar = User::AVATAR_STORAGE;
        $storageDisk = 'public';
        $fileName = 'default.svg'; // Nome único com timestamp

        Storage::put($storageAvatar . DIRECTORY_SEPARATOR . $fileName, $contents, $storageDisk);
        return $fileName; // Retorna o caminho relativo
    }
}
