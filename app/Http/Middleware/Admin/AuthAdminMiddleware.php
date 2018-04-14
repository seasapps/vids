<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Session;

class AuthAdminMiddleware
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

            // only admin can access users
            if(isset($arr_auth_user['role_slug']) && $arr_auth_user['role_slug'] == 'admin')
            {
                return $next($request);
            }
        }

        return redirect('logout');

    }
}
