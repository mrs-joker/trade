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
use MrsJoker\Trade\Facades\Trade;

class Role
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
	 * @param  $roles
	 * @return mixed
	 */

    //        dd(Trade::make('rbac')->executeCommand('user')->hasRole(1, ['test']));
	public function handle($request, Closure $next, $roles)
	{

        $user = $request->user();
        if ($this->auth->guest() || empty($user) || !Trade::make('rbac')->executeCommand('user')->hasRole($user->id, explode('|', $roles))) {
            abort(403);
        }
		return $next($request);
	}
}
