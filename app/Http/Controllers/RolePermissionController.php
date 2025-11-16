<?php
// app/Http/Controllers/Admin/RolePermissionController.php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{Role, Module, Permission};
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{

    public function edit(Role $role)
    {

        $cfg     = config('rbac');
        $modules = $cfg['modules']; // capability matrix
        $binary  = $cfg['binary'];
        $scoped  = $cfg['scoped'];

        // Load DB rows to show names/IDs nicely (optional; view uses cfg for actions)
        $moduleRows = Module::with('submodules')->get()->keyBy('slug'); // for labels if needed

        $current = $role->permissions()->pluck('permissions.name')->toArray();

        return view('admin.roles.permissions_like_shot', [
            'role'       => $role,
            'matrix'     => $modules,
            'binary'     => $binary,
            'scoped'     => $scoped,
            'current'    => $current,
            'moduleRows' => $moduleRows,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $attachNames = [];

        // MODULE-LEVEL: enable/create/listing/view/update/delete
        foreach ((array)$request->input('module', []) as $mSlug => $vals) {
            // binary
            if (($vals['enable'] ?? 'no') === 'yes') $attachNames[] = "$mSlug.enable";
            if (($vals['create'] ?? 'no') === 'yes') $attachNames[] = "$mSlug.create";

            // scoped
            foreach (['listing', 'view', 'update', 'delete'] as $act) {
                $scope = strtolower((string)($vals[$act] ?? 'none'));
                if (in_array($scope, ['own', 'all'], true)) $attachNames[] = "$mSlug.$act.$scope";
            }
        }

        // SUBMODULE-LEVEL
        foreach ((array)$request->input('sub', []) as $key => $vals) {
            // $key format: "module.sub"
            [$mSlug, $sSlug] = explode('.', $key, 2);

            if (($vals['enable'] ?? 'no') === 'yes') $attachNames[] = "$mSlug.$sSlug.enable";
            if (($vals['create'] ?? 'no') === 'yes') $attachNames[] = "$mSlug.$sSlug.create";

            foreach (['listing', 'view', 'update', 'delete'] as $act) {
                $scope = strtolower((string)($vals[$act] ?? 'none'));
                if (in_array($scope, ['own', 'all'], true)) $attachNames[] = "$mSlug.$sSlug.$act.$scope";
            }
        }

        // Map names -> ids
        $permIds = Permission::whereIn('name', $attachNames)->pluck('id')->all();
        $role->permissions()->sync($permIds);

        // (optional) clear per-user permission caches if you implemented caching
        foreach ($role->users as $u) {
            if (method_exists($u, 'flushPermissionCache')) $u->flushPermissionCache();
        }

        return back()->with('success', 'Permissions updated.');
    }
}
