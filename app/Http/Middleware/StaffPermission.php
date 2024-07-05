<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StaffPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        $permissionArray = $request->user()->permissions ?? [];
        if (!in_array($permission, $permissionArray)) return abort(401, "You are not allowed to perform this task");

        return $next($request);
    }
}
