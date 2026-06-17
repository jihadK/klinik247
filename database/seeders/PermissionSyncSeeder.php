<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/**
 * Sync semua permission yang dipakai modul Phase 0-1.7.
 * Idempotent: aman dijalankan berulang kali.
 *
 * Usage: php artisan db:seed --class=PermissionSyncSeeder
 */
class PermissionSyncSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $catalog = [
            // Module => [permissions]
            'master' => [
                'sites.view', 'sites.update',
                'users.view', 'users.create', 'users.update', 'users.delete',
                'roles.view', 'roles.create', 'roles.update', 'roles.delete',
                'doctors.view', 'doctors.create', 'doctors.update', 'doctors.delete',
                'services.view', 'services.create', 'services.update', 'services.delete',
                'medicines.view', 'medicines.create', 'medicines.update', 'medicines.delete',
            ],
            'patient' => [
                'patients.view', 'patients.create', 'patients.update', 'patients.delete',
            ],
            'visit' => [
                'visits.view', 'visits.create', 'visits.update', 'visits.delete',
            ],
            'kb' => [
                'kb.view', 'kb.create', 'kb.update', 'kb.delete',
            ],
            'anc' => [
                'anc.view', 'anc.create', 'anc.update', 'anc.delete',
            ],
            'inc' => [
                'inc.view', 'inc.create', 'inc.update', 'inc.delete',
            ],
            'pnc' => [
                'pnc.view', 'pnc.create', 'pnc.update', 'pnc.delete',
            ],
            'kn' => [
                'kn.view', 'kn.create', 'kn.update', 'kn.delete',
            ],
            'child' => [
                'child.view', 'child.create', 'child.update', 'child.delete',
            ],
        ];

        $rows = [];
        foreach ($catalog as $module => $names) {
            foreach ($names as $name) {
                $rows[] = [
                    'name'         => $name,
                    'display_name' => $this->humanize($name),
                    'module'       => $module,
                    'description'  => null,
                    'is_active'    => true,
                ];
            }
        }

        // Upsert: insert kalau belum ada, update display_name/module kalau ada
        DB::table('tbm_permissions')->upsert(
            $rows,
            ['name'],
            ['display_name', 'module', 'is_active']
        );

        $this->command->info('✅ Synced '.count($rows).' permissions.');

        // Grant SEMUA permission ke role 'admin' & 'superadmin' (kalau ada)
        $this->grantAllTo(['admin', 'superadmin', 'super_admin', 'super-admin']);
    }

    private function grantAllTo(array $roleSlugs): void
    {
        $permIds = DB::table('tbm_permissions')->pluck('id', 'name');
        $now     = Carbon::now();

        foreach ($roleSlugs as $slug) {
            $role = DB::table('tbm_roles')->where('name', $slug)->first();
            if (! $role) continue;

            $existing = DB::table('tbm_role_permissions')
                ->where('role_id', $role->id)
                ->pluck('permission_id')
                ->all();

            $toInsert = [];
            foreach ($permIds as $permName => $permId) {
                if (! in_array($permId, $existing)) {
                    $toInsert[] = [
                        'role_id'       => $role->id,
                        'permission_id' => $permId,
                        'granted_at'    => $now,
                    ];
                }
            }

            if ($toInsert) {
                DB::table('tbm_role_permissions')->insert($toInsert);
                $this->command->info("   → Granted ".count($toInsert)." new perms to role '{$slug}'.");
            }
        }
    }

    private function humanize(string $name): string
    {
        [$module, $action] = explode('.', $name) + [1 => ''];
        $actionMap = [
            'view'   => 'Lihat',
            'create' => 'Tambah',
            'update' => 'Ubah',
            'delete' => 'Hapus',
        ];
        $moduleMap = [
            'sites'     => 'Klinik',
            'users'     => 'Pengguna',
            'roles'     => 'Peran',
            'doctors'   => 'Dokter',
            'services'  => 'Layanan',
            'medicines' => 'Obat',
            'patients'  => 'Pasien',
            'visits'    => 'Kunjungan',
            'kb'        => 'KB',
            'anc'       => 'ANC',
            'inc'       => 'INC (Persalinan)',
            'pnc'       => 'PNC (Nifas)',
            'kn'        => 'KN (Neonatus)',
            'child'     => 'Anak/Imunisasi',
        ];
        return ($actionMap[$action] ?? ucfirst($action)) . ' ' . ($moduleMap[$module] ?? ucfirst($module));
    }
}
