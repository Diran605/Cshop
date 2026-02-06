<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPermissionsBranch
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            setPermissionsTeamId(null);
            return $next($request);
        }

        if ((string) ($user->role ?? '') === 'super_admin') {
            setPermissionsTeamId(null);
            return $next($request);
        }

        $branchId = (int) ($user->branch_id ?? 0);
        setPermissionsTeamId($branchId > 0 ? $branchId : null);

        return $next($request);
    }
}
