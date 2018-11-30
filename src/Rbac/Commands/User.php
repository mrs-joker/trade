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
        $item['created_by'] = app('request')->user()->id;
        $item['updated_by'] = app('request')->user()->id;
        $item['description'] = isset($item['description']) ? $item['description'] : '';
        $error = $this->validator($item)->errors()->first();
        if (empty($error)) {
            $model = $this->createModel($this->config['role']);
            $model->name = $item['name'];
            $model->display_name = $item['display_name'];
            $model->description = $item['description'];
            $model->created_by = $item['created_by'];
            $model->updated_by = $item['updated_by'];

            if ($model->save()) {
                Cache::tags($model->getTable())->flush();
                return true;
            }
            throw new NotSupportedException("The server is busy. Please try again later.");
        }
        throw new NotSupportedException($error);
    }

    public function setDefaultValues($item = [], $model = null)
    {

        if (isset($item['id']) && !empty($model)) {
            $item['name'] = $item['name'] ?? $model->name;
            $item['email'] = $item['email'] ?? $model->email;
            $item['password'] = $item['password'];

        } else {
            $item['name'] = $item['name'] ?? '';
            $item['email'] = $item['email'] ?? '';
            $item['password'] = '';
        }

        return $item;
    }


    public function update($item)
    {
        if (isset($item['id'])) {

            $model = $this->createModel($this->config['user'])->findOrFail($item['id']);
            $item = $this->setDefaultValues($item, $model);
            $error = $this->validator($item, $model)->errors()->first();
            if (empty($error)) {
                $model->name = $item['name'];
                $model->email = $item['email'];
                if (isset($item['password']) && !empty($item['password'])) {
                    $model->password = bcrypt($item['password']);
                }
                if ($model->save()) {
                    Cache::tags($model->getTable())->flush();
                    return true;
                }
                throw new NotSupportedException("The server is busy. Please try again later.");
            }
            throw new NotSupportedException($error);
        }
        throw new NotSupportedException("id is required");
    }

    public function validator($item, $model = null)
    {

        if (isset($item['id']) && !empty($item['id']) && !empty($model)) {

            $rules['name'] = [function ($attribute, $value, $fail) use ($model) {
                if (!empty($value) && $model->$attribute != $value) {
                    $user = $this->createModel($this->config['user'])->where('name', $value)->first();
                    if (!empty($user)) {
                        $fail('User name already exists.');
                    }
                }
            }];
            $rules['email'] = [function ($attribute, $value, $fail) use ($model) {
                if (!empty($value) && $model->$attribute != $value) {
                    $user = $this->createModel($this->config['user'])->where('email', $value)->first();
                    if (!empty($user)) {
                        $fail('User mailbox already exists');
                    }
                }
            }];
            return \Illuminate\Support\Facades\Validator::make($item, $rules);
        }
        return false;
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

    public function cachedRolesAll()
    {

        $model = $this->createModel($this->config['role']);
        $tableName = $model->getTable();
        $tag = [
            $tableName,
        ];
        $cacheKey = $this->config['cache_prefix'] . $tableName . '_all';
        return Cache::tags($tag)->remember($cacheKey, 60 * 24 * 30, function () use ($model) {
            return $model->get();
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

    public function detachRoles($userId)
    {

        $role = $this->cachedRoles($userId);
        if (!$role->isEmpty()) {
            $roles = array_pluck($role->toArray(), 'id');
            if (!empty($roles) && is_array($roles)) {
                $this->detachRole($userId, $roles);
            }
        }
    }

    public function destory($userId)
    {

        $model = $this->createModel($this->config['user'])->findOrFail($userId);

        $tag = [
            $model->getTable(),
            $this->config['table_role_user'],
            $this->createModel($this->config['role'])->getTable()
        ];
        Cache::tags($tag)->flush();
        return $model->delete();
    }


    public function restore($userId)
    {   //soft delete undo's

        $model = $this->createModel($this->config['user'])->onlyTrashed()->findOrFail($userId);
        $model->restore();
        Cache::tags($model->getTable())->flush();
        return true;
    }
}
