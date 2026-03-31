<?php

namespace App\Http\Middleware;

use Closure;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\DB;

class CheckSingleDevice
{
    /**
     * Handle an incoming request.
     *
     * Enforce single-device login by comparing the session token
     * stored in the user's session with the one in the database.
     * If they don't match, it means another device logged in,
     * so we kick this device out.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Admin::user();

        if ($user) {
            $currentToken = $request->session()->get('admin_session_token');
            $dbToken = DB::table('admin_users')->where('id', $user->id)->value('session_token');

            if ($currentToken && $dbToken && $currentToken !== $dbToken) {
                Admin::guard()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $userType = $user->type ?? 'admin';
                $loginUrl = match ($userType) {
                    'bd' => '/bd/login',
                    'superadmin', 'sub_super_admin' => '/superadmin/login',
                    'area-manager', 'sub_area_manager' => '/areaManager/login',
                    default => '/admin/login',
                };

                return redirect($loginUrl)->withErrors([
                    'error' => 'Your account has been logged in from another device. You have been logged out.',
                ]);
            }
        }

        return $next($request);
    }
}
