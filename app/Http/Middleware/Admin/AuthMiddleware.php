<?php

namespace App\Http\Middleware\Admin;

use Closure;

use Session;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {       
        if(Session::has('arr_auth_user'))
        {   
            $arr_auth_user = Session::get('arr_auth_user');

            // Following variables are accessible in views where this middleware is applied.
            view()->share('role_slug',$arr_auth_user['role_slug']);
            view()->share('role_name',$arr_auth_user['role_name']);
            return $next($request);
        }

        return redirect('logout');
    }
}
