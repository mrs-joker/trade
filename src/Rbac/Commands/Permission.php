<?php

namespace MrsJoker\Trade\Rbac\Commands;

use Illuminate\Support\Facades\Cache;
use MrsJoker\Trade\Exception\NotSupportedException;
use MrsJoker\Trade\Rbac\AbstractCommands;

class Permission extends AbstractCommands
{

    protected function validator($item)
    {
        if (isset($item['id']) && !empty($item['id'])) {
            $model = $this->createModel($this->config['permission'])->findOrFail($item['id']);
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
        if (isset($item['id'])) {
            $item['updated_by'] = app('request')->user()->id;

            $error = $this->validator($item)->errors()->first();
            if (empty($error)) {
                $model = $this->createModel($this->config['permission'])->findOrFail($item['id']);
                $model->display_name = $item['display_name'];
                $model->description = isset($item['description']) ? $item['description'] : '';
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

        $error = $this->validator($item)->errors()->first();
        if (empty($error)) {
            $model = $this->createModel($this->config['permission']);
            $model->name = $item['name'];
            $model->display_name = $item['display_name'];
            $model->description = isset($item['description']) ? $item['description'] : '';
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


    public function destory($permissionId)
    {
        $model = $this->createModel($this->config['permission'])->findOrFail($permissionId);
        return $model->delete();
    }


    public function restore($permissionId)
    {   //soft delete undo's

        $model = $this->createModel($this->config['permission'])->onlyTrashed()->findOrFail($permissionId);
        return $model->restore();
    }

}