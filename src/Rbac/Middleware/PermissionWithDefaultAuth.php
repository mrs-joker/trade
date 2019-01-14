<?php

namespace MrsJoker\Trade\Rbac\Middleware;

/**
 * This file is part of Rbac,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Rbac
 */

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Route;
use MrsJoker\Trade\Facades\Trade;

class PermissionWithDefaultAuth
{
    protected $auth;

    /**
     * Creates a new instance of the middleware.
     *
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        if (!Trade::make('rbac')->executeCommand('user')->can($user->id, Route::currentRouteName())) {
            abort(403);
        }
        return $next($request);
    }
}
