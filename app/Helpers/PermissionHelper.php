<?php

use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use App\Models\Submodule;
use App\Models\Module;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

if (!function_exists('canAccess')) {

    function canAccess($roleId, $slug)
    {
        return Module::where('role_id', $roleId)
            ->where('slug', $slug)
            ->whereIn('is_active', ['yes'])
            ->exists();
    }
}


if (!function_exists('canAccessSubModule')) {

    function canAccessSubModule($roleId, $slug)
    {
        return Submodule::where('role_id', $roleId)
            ->where('slug', $slug)
            ->whereIn('is_active', ['yes', 'all'])
            ->exists();
    }
}

if (!function_exists('canCreate')) {

    function canCreate($roleId, $slug)
    {
        return Submodule::where('role_id', $roleId)
            ->where('slug', $slug)
            ->where('is_active', 'yes')
            ->exists();
    }
}

if (!function_exists('canImport')) {

    function canImport($roleId, $slug)
    {
        return Submodule::where('role_id', $roleId)
            ->where('slug', $slug)
            ->whereIn('is_active', ['yes', 'all'])
            ->exists();
    }
}

if (!function_exists('getAccess')) {
    function getAccess($roleId, $slug)
    {
        // Super admin bypass
        if ($roleId == 1) {
            return 'all';
        }

        $sub = Submodule::where('role_id', $roleId)
            ->where('slug', $slug)
            ->first();

        return $sub ? $sub->is_active : 'none';
    }
}

if (!function_exists('canDo')) {
    function canDo($roleId, $slug, $ownerId = null)
    {
        // 🔥 SUPER ADMIN → FULL ACCESS
        if ($roleId == 1) {
            return true;
        }

        $level = getAccess($roleId, $slug);

        if ($level === 'all' || $level === 'yes') {
            return true;
        }

        if ($level === 'own' && auth()->id() == $ownerId) {
            return true;
        }

        return false;
    }
}
