<?php

namespace App\Http\Middleware;

use App\Helpers\Utilities;
use App\Models\UserFlag;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as CodeOf;
class CheckThingAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (in_array(UserFlag::FLAG_ADMIN,Utilities::get_logged_user()->getTags()) ) {
            return $next($request);
        }
        abort(CodeOf::HTTP_FORBIDDEN);
    }
}
