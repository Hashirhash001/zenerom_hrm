<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckLoginSession
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
        // If the 'uid' session variable is not present, redirect to the login page.
        if (!$request->session()->has('uid')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
