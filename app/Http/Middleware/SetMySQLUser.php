<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SetMySQLUser
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            DB::statement('SET @user_id = ?', [Auth::id()]);
        }

        return $next($request);
    }
}
