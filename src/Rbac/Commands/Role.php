<?php

namespace MrsJoker\Trade\Rbac\Commands;

use Illuminate\Support\Facades\Cache;
use MrsJoker\Trade\Exception\NotSupportedException;
use MrsJoker\Trade\Rbac\AbstractCommands;

class Role extends AbstractCommands
{

    protected function validator($item)
    {
        if (isset($item['id']) && !empty($item['id'])) {
            $model = $this->createModel($this->config['role'])->findOrFail($item['id']);
            $rules['name'] = [function ($attribute, $value, $fail) use ($model) {
                if (!empty($value) && $model->$attribute != $value) {
                    $fail(':attribute can not be change');
                }
            }];
        } else {
            $model = $this->createModel($this->config['role']);
            $rules['name'] = "required|string|max:100|unique:{$model->getTable()}";
            $rules['created_by'] = 'required|exists:users,id';
        }

        $rules['display_name'] = 'required|string|max:255';
        $rules['description'] = 'string|max:512';
        $rules['updated_by'] = 'required|exists:users,id';

        return \Illuminate\Support\Facades\Validator::make($item, $rules);

    }


    public function cachedPermissions($roleId)
    {

        $model = $this->createModel($this->config['role'])->findOrFail($roleId);
        $tableName = $model->getTable();
        $cacheKey = $this->config['cache_prefix'] . $tableName . $roleId;
        $tag = [
            $tableName,
            $this->config['table_permission_role'],
            $this->createModel($this->config['permission'])->getTable()
        ];

        return Cache::tags($tag)->remember($cacheKey, 60 * 24 * 30, function () use ($model) {
            return $model->perms()->get();
        });
    }

    /**
     * Detach ass permissions from current role.
     *
     * @param object|array $permission
     *
     * @return void
     */
    public function detachPermissions($roleId)
    {

        $permission = $this->cachedPermissions($roleId);
        if (!empty($permission)) {
            $permissionIds = array_pluck($permission->toArray(), 'id');
            if (!empty($permissionIds) && is_array($permissionIds)) {
                $this->detachPermission($roleId, $permissionIds);
            }
        }
        Cache::tags($this->config['table_permission_role'])->flush();
    }

    /**
     * Detach permission from current role.
     *
     * @param object|array $permission
     *
     * @return void
     */
    public function detachPermission($roleId, $permissionId)
    {
        $model = $this->createModel($this->config['role'])->findOrFail($roleId);
        $model->perms()->detach($permissionId);
        Cache::tags($this->config['table_permission_role'])->flush();
    }

    /**
     * Attach permission to current role.
     *
     * @param object|array $permission
     *
     * @return void
     */
    public function attachPermission($roleId, $permissionId)
    {
        $model = $this->createModel($this->config['role'])->findOrFail($roleId);
        $model->perms()->attach($permissionId);
        Cache::tags($this->config['table_permission_role'])->flush();
    }

    /**
     * @param $roleId
     * @param bool $detach
     * @return mixed
     * @throws NotSupportedException
     */
    public function destory($roleId, $detach = false)
    {

        $model = $this->createModel($this->config['role'])->findOrFail($roleId);
        if ($detach) {
            $permission = $this->cachedPermissions($roleId);
            if (!empty($permission)) {
                $permissionIds = array_pluck($permission->toArray(), 'id');
                if (!empty($permissionIds) && is_array($permissionIds)) {
                    $this->detachPermission($roleId, $permissionIds);
                }
            }
        }

        $tag = [
            $model->getTable(),
            $this->config['table_permission_role'],
            $this->createModel($this->config['permission'])->getTable()
        ];
        Cache::tags($tag)->flush();
        return $model->delete();
    }


    public function update($item)
    {
        if (isset($item['id'])) {
            $item['updated_by'] = app('request')->user()->id;
            $item['description'] = isset($item['description']) ? $item['description'] : '';
            $error = $this->validator($item)->errors()->first();
            if (empty($error)) {
                $model = $this->createModel($this->config['role'])->findOrFail($item['id']);
                $model->display_name = $item['display_name'];
                $model->description = $item['description'];
                $model->updated_by = $item['updated_by'];

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


    public function restore($roleId)
    {   //soft delete undo's

        $model = $this->createModel($this->config['role'])->onlyTrashed()->findOrFail($roleId);
        $model->restore();
        Cache::tags($model->getTable())->flush();
        return true;
    }

    /**
     * Checks if the role has a permission by its name.
     *
     * @param string|array $name Permission name or array of permission names.
     * @param bool $requireAll All permissions in the array are required.
     *
     * @return bool
     */
    public function hasPermission($roleId, $name, $requireAll = false)
    {
        if (is_array($name)) {
            foreach ($name as $permissionName) {
                $hasPermission = $this->hasPermission($roleId, $permissionName);
                if ($hasPermission && !$requireAll) {
                    return true;
                } elseif (!$hasPermission && $requireAll) {
                    return false;
                }
            }
            // If we've made it this far and $requireAll is FALSE, then NONE of the permissions were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the permissions were found.
            // Return the value of $requireAll;
            return $requireAll;
        } else {
            foreach ($this->cachedPermissions($roleId) as $permission) {
                if ($permission->name == $name) {
                    return true;
                }
            }
        }
        return false;
    }


}