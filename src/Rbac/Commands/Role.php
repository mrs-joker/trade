<?php

namespace MrsJoker\Trade\Rbac\Commands;

use Illuminate\Support\Facades\Cache;
use MrsJoker\Trade\Exception\NotSupportedException;
use MrsJoker\Trade\Rbac\AbstractCommands;

class Role extends AbstractCommands
{

    protected function validator($item, $model = null)
    {
        if (isset($item['id']) && !empty($item['id']) && !empty($model)) {

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

    public function cachedAllPermissions()
    {
        $model = $this->createModel($this->config['permission']);
        $tableName = $model->getTable();
        $cacheKey = $this->config['cache_prefix'] . $tableName;
        $tag = [
            $tableName
        ];
        return Cache::tags($tag)->remember($cacheKey, 60 * 24 * 30, function () use ($model) {
            return $model->get();
        });
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
        if (!$permission->isEmpty()) {
            $permissionIds = array_pluck($permission->toArray(), 'id');
            if (!empty($permissionIds) && is_array($permissionIds)) {
                $this->detachPermission($roleId, $permissionIds);
            }
        }
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

    /**
     * @param $item
     * @return bool|mixed
     * @throws NotSupportedException
     */
    public function update($item)
    {
        if (isset($item['id'])) {

            $model = $this->createModel($this->config['role'])->findOrFail($item['id']);
            $item = $this->setDefaultValues($item, $model);
            $error = $this->validator($item, $model)->errors()->first();
            if (empty($error)) {
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

        $item = $this->setDefaultValues($item);
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

    /**
     * 设置默认数据
     * @param $item
     * @param null $model
     * @return mixed
     */
    public function setDefaultValues($item, $model = null)
    {

        $user = app('request')->user();
        if (isset($item['id']) && !empty($model)) {
            $item['name'] = $item['name'] ?? $model->name;
            $item['display_name'] = $item['display_name'] ?? $model->display_name;
            $item['description'] = $item['description'] ?? $model->description;
            $item['updated_by'] = $item['updated_by'] ?? $user->id;
        } else {
            $item['name'] = $item['name'] ?? str_random(60);
            $item['display_name'] = $item['display_name'] ?? '';
            $item['description'] = $item['description'] ?? '';
            $item['created_by'] = $item['created_by'] ?? $user->id;
            $item['updated_by'] = $item['updated_by'] ?? $user->id;
        }
        return $item;

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

    public function save($item)
    {
        if (isset($item['id']) && !empty($item)) {
            $this->update($item);
        } else {
            $this->add($item);
        }
    }

}