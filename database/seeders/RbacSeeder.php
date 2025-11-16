<?php
// database/seeders/RbacSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Module, Submodule, Permission};

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        $cfg     = config('rbac');
        $binary  = $cfg['binary'];
        $scoped  = $cfg['scoped'];
        $modules = $cfg['modules'];

        foreach ($modules as $mSlug => $m) {
            // upsert module row
            $module = Module::firstOrCreate(['slug' => $mSlug], ['name' => $m['label']]);

            // MODULE actions subset
            foreach ($m['actions'] as $act) {
                if (in_array($act, $binary, true)) {
                    Permission::firstOrCreate(['name' => "$mSlug.$act"]);
                } elseif (in_array($act, $scoped, true)) {
                    Permission::firstOrCreate(['name' => "$mSlug.$act.own"]);
                    Permission::firstOrCreate(['name' => "$mSlug.$act.all"]);
                }
            }

            // SUBMODULES (optional)
            foreach ($m['submodules'] as $sSlug => $s) {
                $sub = Submodule::firstOrCreate(
                    ['module_id' => $module->id, 'slug' => $sSlug],
                    ['name' => $s['label']]
                );

                foreach ($s['actions'] as $act) {
                    if (in_array($act, $binary, true)) {
                        Permission::firstOrCreate(['name' => "$mSlug.$sSlug.$act"]);
                    } elseif (in_array($act, $scoped, true)) {
                        Permission::firstOrCreate(['name' => "$mSlug.$sSlug.$act.own"]);
                        Permission::firstOrCreate(['name' => "$mSlug.$sSlug.$act.all"]);
                    }
                }
            }
        }
    }
}
