<?php

namespace MrsJoker\Trade\Rbac\Commands;

/**
 * This file is part of Rbac,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Rbac
 */

use Illuminate\Support\Facades\Cache;
use MrsJoker\Trade\Exception\NotSupportedException;
use MrsJoker\Trade\Rbac\AbstractCommands;

class User extends AbstractCommands
{

    public function add($item)
    {
        // TODO: Implement add() method.
    }

    public function update($item)
    {
        // TODO: Implement update() method.
    }

    public function validator($item)
    {
        // TODO: Implement validator() method.
    }

    /**
     * Big block of caching functionality.
     *
     * @return mixed Roles
     */
    public function cachedRoles($userId)
    {

        $model = $this->createModel($this->config['user'])->findOrFail($userId);
        $tableName = $model->getTable();
        $cacheKey = $this->config['cache_prefix'] . $tableName . $userId;
        $tag = [
            $tableName,
            $this->config['table_role_user'],
            $this->createModel($this->config['role'])->getTable()
        ];

        return Cache::tags($tag)->remember($cacheKey, 60 * 24 * 30, function () use ($model) {
            return $model->roles()->get();
        });

    }

    /**
     * Checks if the user has a role by its name.
     *
     * @param string|array $name Role name or array of role names.
     * @param bool $requireAll All roles in the array are required.
     *
     * @return bool
     */
    public function hasRole($userId, $name, $requireAll = false)
    {
        if (is_array($name)) {
            foreach ($name as $roleName) {
                $hasRole = $this->hasRole($userId, $roleName);
                if ($hasRole && !$requireAll) {
                    return true;
                } elseif (!$hasRole && $requireAll) {
                    return false;
                }
            }
            // If we've made it this far and $requireAll is FALSE, then NONE of the roles were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the roles were found.
            // Return the value of $requireAll;
            return $requireAll;
        } else {
            foreach ($this->cachedRoles($userId) as $role) {
                if ($role->name == $name) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check if user has a permission by its name.
     *
     * @param string|array $permission Permission string or array of permissions.
     * @param bool $requireAll All permissions in the array are required.
     *
     * @return bool
     */
    public function can($userId, $permission, $requireAll = false)
    {

        $roleCommands = new Role($this->config);

        if (is_array($permission)) {
            foreach ($permission as $permName) {
                $hasPerm = $this->can($userId, $permName);
                if ($hasPerm && !$requireAll) {
                    return true;
                } elseif (!$hasPerm && $requireAll) {
                    return false;
                }
            }
            // If we've made it this far and $requireAll is FALSE, then NONE of the perms were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the perms were found.
            // Return the value of $requireAll;
            return $requireAll;
        } else {

            foreach ($this->cachedRoles($userId) as $role) {
                // Validate against the Permission table
                foreach ($roleCommands->cachedPermissions($role->id) as $perm) {
                    if (str_is($permission, $perm->name)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }


    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $role
     */
    public function attachRole($userId, $roleId)
    {
        $model = $this->createModel($this->config['user'])->findOrFail($userId);
        $model->roles()->attach($roleId);
        Cache::tags($this->config['table_role_user'])->flush();
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $role
     */
    public function detachRole($userId, $roleId)
    {
        $model = $this->createModel($this->config['user'])->findOrFail($userId);
        $model->roles()->detach($roleId);
        Cache::tags($this->config['table_role_user'])->flush();
    }

}
