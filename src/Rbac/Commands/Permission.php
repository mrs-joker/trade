<?php

namespace MrsJoker\Trade\Rbac\Commands;

use Illuminate\Support\Facades\Cache;
use MrsJoker\Trade\Exception\NotSupportedException;
use MrsJoker\Trade\Rbac\AbstractCommands;

class Permission extends AbstractCommands
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
            $model = $this->createModel($this->config['permission']);
            $rules['name'] = "required|string|max:100|unique:{$model->getTable()}";
            $rules['created_by'] = 'required|exists:users,id';
        }

        $rules['display_name'] = 'required|string|max:255';
        $rules['description'] = 'string|max:512';
        $rules['updated_by'] = 'required|exists:users,id';

        return \Illuminate\Support\Facades\Validator::make($item, $rules);

    }

    public function update($item)
    {
        if (isset($item['id']) && !empty($item['id'])) {

            $model = $this->createModel($this->config['permission'])->findOrFail($item['id']);
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

    public function add($item)
    {

        $item = $this->setDefaultValues($item);
        $error = $this->validator($item)->errors()->first();
        if (empty($error)) {
            $model = $this->createModel($this->config['permission']);
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

    public function save($item)
    {
        if (isset($item['id']) && !empty($item)) {
            $this->update($item);
        } else {
            $this->add($item);
        }
    }


    public function destory($permissionId)
    {
        $model = $this->createModel($this->config['permission'])->findOrFail($permissionId);
        if ($model->delete()){
            Cache::tags($model->getTable())->flush();
            return true;
        }
    }


    public function restore($permissionId)
    {   //soft delete undo's

        $model = $this->createModel($this->config['permission'])->onlyTrashed()->findOrFail($permissionId);
        if ($model->restore()){
            Cache::tags($model->getTable())->flush();
            return true;
        }
    }

}