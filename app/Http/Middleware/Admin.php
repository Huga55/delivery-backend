<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('api-key');
        if(isset($key) && $key) {
            $user = \App\Models\Admin::where('api_key', $key)->first();
            if(isset($user)) {
                return $next($request);
            }
        }
        return response()->json(['success' => false, 'error' => 'You are not auth'], 200);
    }
}
