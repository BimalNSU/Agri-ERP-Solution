<?php

namespace App\Http\Middleware;

use Closure;

class User
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
        if(!Auth::check()){
            return redirect()->route('login');
        }
        if(Auth::user()->role == 'user')
        {
<<<<<<< HEAD
            return redirect()->route('user');
=======
            return $next($request);
>>>>>>> 5ea729515e4e82473d52a24596803fcc4be2e0a5
        }
        if(Auth::user()->role == 'admin')
        {
            return redirect()->route('admin');
<<<<<<< HEAD
        }
=======
        }  
>>>>>>> 5ea729515e4e82473d52a24596803fcc4be2e0a5
    }
}
