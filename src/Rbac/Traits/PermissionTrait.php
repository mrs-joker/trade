<?php

namespace MrsJoker\Trade\Rbac\Traits;
/**
 * This file is part of Rbac,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Rbac
 */

use Illuminate\Support\Facades\Config;

trait PermissionTrait
{
    /**
     * Boot the permission model
     * Attach event listener to remove the many-to-many records when trying to delete
     * Will NOT delete any records if the permission model uses soft deletes.
     *
     * @return void|bool
     */
    public static function boot()
    {
        parent::boot();
        static::deleting(function($permission) {
            if (!method_exists(Config::get('entrust.permission'), 'bootSoftDeletes')) {
                $permission->roles()->sync([]);
            }
            return true;
        });
    }
}
