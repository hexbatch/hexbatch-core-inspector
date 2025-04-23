<?php

namespace App\Http\Middleware;

use App\Helpers\TestOwners\OwnerFromUser;
use App\Helpers\Utilities;
use Closure;
use Hexbatch\Things\Interfaces\IThingOwner;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
class SetThingOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $owner = new OwnerFromUser(user: Utilities::get_logged_user());
        App::bind(IThingOwner::class, function() use($owner) {
            return $owner;
        });
        return $next($request);
    }
}
