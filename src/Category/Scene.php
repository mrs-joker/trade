<?php

namespace MrsJoker\Trade\Category;

use Illuminate\Support\Facades\Cache;
use MrsJoker\Trade\AbstractScene;
use MrsJoker\Trade\Exception\NotSupportedException;

class Scene extends AbstractScene
{
    /**
     * 树状拉取数据
     * @param int $parentId
     * @return array
     */
    public function runTrees($parentId = 0, $items = [])
    {
        if (empty($items)) {
            return $items;
        }

        $childItems = [];
        $i = 0;
        foreach ($items as $key => $val) {
            if ($val->parent_id == $parentId) {
                $childItems[$i] = $val->toArray();
                $childItems[$i]['text'] = $val->name;
                $childItems[$i]['icon'] = '';
                $childItems[$i]['tags'] = ['排序：' . $val->sort_order];

                $child = $this->runTrees($val->id, $items);
                if (!empty($child)) {
                    $childItems[$i]['nodes'] = $child;
                }
                $i++;
            }
        }

        return $childItems;

    }


    /**
     * @return mixed
     * @throws NotSupportedException
     */
    public function cachedCategorys(array $select = [], $parentId = -1)
    {

        $model = $this->createModel();
        $tableName = $model->getTable();
        $cacheKey = $this->config['cache_prefix'] . $tableName;

        //选择栏目
        if (!empty($select)) {
            $model = $model->select($select);
            $cacheKey = $cacheKey . implode('-', $select);
        }
        //选择parentId作为条件
        if ($parentId >= 0) {
            $model = $model->where('parent_id', $parentId);
            $cacheKey = $cacheKey . '_' . $parentId;
        }

        return Cache::tags([$tableName])->remember($cacheKey, 60 * 24 * 30, function () use ($model) {
            return $model->orderBy('sort_order', 'ASC')->get();
        });
    }

    /**
     * 数据校验
     * @param $item
     * @return \Illuminate\Contracts\Validation\Validator|mixed
     * @throws \MrsJoker\Trade\Exception\NotSupportedException
     */
    protected function validator($item)
    {

        $user = $this->createModel($this->config['user']);
        if (isset($item['id']) && !empty($item['id'])) {

            $model = $this->createModel()->findOrFail($item['id']);
            $rules['name'] = 'string|max:255';
            $rules['category_code'] = [function ($attribute, $value, $fail) use ($model) {
                if (!empty($value) && $model->$attribute != $value) {
                    $fail(':attribute can not be change');
                }
            }];

            $rules['parent_id'] = [function ($attribute, $value, $fail) use ($model) {

                if ($model->id == $value) {
                    $fail(':attribute can not be your own father!');
                }
                $dot = array_dot($this->runTrees($model->id, $this->cachedCategorys(['id', 'parent_id'])));

                foreach ($dot as $k => $v) {
                    if (str_is('*.id', $k) && $v == (int)$value) {
                        $fail(':attribute can not be your child\'s father!');
                    }
                }
            }, function ($attribute, $value, $fail) {
                if (empty($this->createModel()->find($value)) && $value != 0) {
                    $fail(':attribute non-existent!');
                }
            }];
            $rules['updated_by'] = "required|exists:{$user->getTable()},id";
        } else {
            $model = $this->createModel();
            $rules['name'] = 'required|string|max:255';
            $rules['category_code'] = "required|string|max:100|unique:{$model->getTable()}";
            $rules['parent_id'] = [function ($attribute, $value, $fail) use ($model) {
                $find = $model->find($value);
                if (empty($find) && $value != 0) {
                    $fail(':attribute needs more cowbell!');
                }
            }];
            $rules['created_by'] = "required|exists:{$user->getTable()},id";
            $rules['updated_by'] = "required|exists:{$user->getTable()},id";
        }

        $rules['additional_data'] = [function ($attribute, $value, $fail) {
            if (!is_array($value)) {
                $fail(':attribute must be array!');
            }
        }];
        $rules['sort_order'] = 'numeric|max:99999999';
        return \Illuminate\Support\Facades\Validator::make($item, $rules);

    }


    /**
     * @param $item
     * @return bool|mixed
     * @throws NotSupportedException
     */
    public function update($item)
    {
        $user = app('request')->user();
        if (empty($user)) {
            throw new NotSupportedException("please login");
        }

        $item['updated_by'] = $user->id;

        if (isset($item['id']) && !empty($item['id'])) {


            $error = $this->validator($item)->errors()->first();
            if (empty($error)) {
                $model = $this->createModel()->findOrFail($item['id']);
                $model->parent_id = isset($item['parent_id']) && !empty($item['parent_id']) ? $item['parent_id'] : $model->parent_id;
                $model->name = isset($item['name']) && !empty($item['name']) ? $item['name'] : $model->name;
                $model->additional_data = isset($item['additional_data']) && !empty($item['additional_data']) ? $item['additional_data'] : $model->additional_data;
                $model->sort_order = isset($item['sort_order']) && !empty($item['sort_order']) ? $item['sort_order'] : $model->sort_order;
                $model->updated_by = $item['updated_by'];

                if ($model->save()) {
                    Cache::tags($model->getTable())->flush();
                    return true;
                }

                throw new NotSupportedException("The server is busy. Please try again later.");
            }
            throw new NotSupportedException($error);
        }
        throw new NotSupportedException("id can not be null.");
    }

    /**
     * @param $item
     * @return bool|mixed
     * @throws NotSupportedException
     */
    public function add($item)
    {

        $user = app('request')->user();
        if (empty($user)) {
            throw new NotSupportedException("please login");
        }

        $item['category_code'] = isset($item['category_code']) && !empty($item['category_code']) ? $item['category_code'] : str_random(60);
        $item['created_by'] = $user->id;
        $item['updated_by'] = $user->id;

        $error = $this->validator($item)->errors()->first();
        if (empty($error)) {
            $model = $this->createModel();
            $model->parent_id = $item['parent_id'];
            $model->category_code = $item['category_code'];
            $model->name = $item['name'];
            $model->additional_data = isset($item['additional_data']) ? $item['additional_data'] : [];
            $model->sort_order = isset($item['sort_order']) ? $item['sort_order'] : 999;
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


    public function destory($id)
    {
        $user = app('request')->user();
        if (empty($user)) {
            throw new NotSupportedException("please login");
        }
        $model = $this->createModel()->findOrFail($id);
        if ($model->delete()) {
            Cache::tags($model->getTable())->flush();
            return true;
        }
    }


    public function restore($id)
    {   //soft delete undo's
        $user = app('request')->user();
        if (empty($user)) {
            throw new NotSupportedException("please login");
        }

        $model = $this->createModel()->onlyTrashed()->findOrFail($id);

        if ($model->restore()) {
            Cache::tags($model->getTable())->flush();
            return true;
        }
    }


}