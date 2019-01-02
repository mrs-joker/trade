<?php

namespace MrsJoker\Trade\Tag\Commands;

use App\Models\TagQuantification;
use Illuminate\Support\Facades\Cache;
use MrsJoker\Trade\Exception\NotSupportedException;
use MrsJoker\Trade\Rbac\AbstractCommands;

class Node extends AbstractCommands
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

                $leaf = '树枝结点';
                if ($val->is_leaf_node) {
                    $leaf = '叶子结点';
                }
                $scene = $this->config['scene'][$val->scene] ?? '';

                $childSelect = '子集非必选';
                if ($val->child_select) {
                    $childSelect = '子集必选';
                }
                $childItems[$i]['tags'] = ['排序:' . $val->sort_order, '节点类型:' . $leaf, '场景:' . $scene, $childSelect];
                $child = $this->runTrees($val->id, $items);
                if (!empty($child)) {
                    $childItems[$i]['nodes'] = $child;
                }
                $i++;
            }
        }

        return $childItems;

    }

    protected function validator($item, $model = null)
    {
        $rules['is_leaf_node'] = 'required|boolean';
        $rules['quantification_id'] = "numeric|max:99999999";
        $rules['child_select'] = 'required|boolean';
        $rules['scene'] = [function ($attribute, $value, $fail) {
            if (!isset($this->config['scene'][$value])) {
                $fail(':attribute can not be change');
            }
        }];

        $rules['sort_order'] = 'numeric|max:99999999';
        if (isset($item['id']) && !empty($item['id']) && !empty($model)) {

//            $rules['name'] = [function ($attribute, $value, $fail) use ($model) {
//                if (!empty($value) && $model->$attribute != $value) {
//                    $fail(':attribute can not be change');
//                }
//            }];
        } else {

            $model = $this->createModel($this->config['node']);
            $rules['parent_id'] = [function ($attribute, $value, $fail) use ($model) {
                $find = $model->find($value);
                if (empty($find) && $value != 0) {
                    $fail(':attribute needs more cowbell!');
                }
            }];

            $rules['category_code'] = "required|string|max:100|unique:{$model->getTable()}";
            if ($item['is_leaf_node'] == 0) {
                $rules['name'] = "required|string|max:100|unique:{$model->getTable()}";
            }

            $rules['created_by'] = "required|exists:{$this->createModel($this->config['user'])->getTable()},id,deleted_at,NULL";

        }

//        $rules['display_name'] = 'required|string|max:255';
//        $rules['description'] = 'string|max:512';
//        $rules['updated_by'] = 'required|exists:users,id';

        return \Illuminate\Support\Facades\Validator::make($item, $rules);

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
            $model = $this->createModel($this->config['node'])->findOrFail($item['id']);
            $item = $this->setDefaultValues($item, $model);

            $error = $this->validator($item, $model)->errors()->first();
            if (empty($error)) {

                $model->parent_id = $item['parent_id'] ?? 0;
                if ($item['is_leaf_node'] == 0) {
                    $model->is_leaf_node = 0;
                    $model->quantification_id = 0;
                    $model->child_select = $item['child_select'] ?? 0;
                    $model->name = $item['name'] ?? '';
                } elseif ($item['is_leaf_node'] == 1) {
                    $tag = TagQuantification::findOrFail($item['quantification_id']);
                    $model->is_leaf_node = 1;
                    $model->quantification_id = $tag->id;
                    $model->child_select = 0;
                    $model->name = $tag->name;
                }
                $model->category_code = $item['category_code'];
                $model->scene = $item['scene'];
                $model->sort_order = $item['sort_order'];
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
            $model = $this->createModel($this->config['node']);
            $model->parent_id = $item['parent_id'] ?? 0;
            if ($item['is_leaf_node'] == 0) {
                $model->is_leaf_node = 0;
                $model->quantification_id = 0;
                $model->child_select = $item['child_select'] ?? 0;
                $model->name = $item['name'] ?? '';
            } elseif ($item['is_leaf_node'] == 1) {
                $tag = TagQuantification::findOrFail($item['quantification_id']);
                $model->is_leaf_node = 1;
                $model->quantification_id = $tag->id;
                $model->child_select = 0;
                $model->name = $tag->name;
            }
            $model->category_code = $item['category_code'];
            $model->scene = $item['scene'];
            $model->sort_order = $item['sort_order'];
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

            $item['parent_id'] = $item['parent_id'] ?? $model->parent_id;
            $item['is_leaf_node'] = $item['is_leaf_node'] ?? $model->is_leaf_node;
            $item['quantification_id'] = $item['quantification_id'] ?? $model->quantification_id;
            $item['child_select'] = $item['child_select'] ?? $model->child_select;
            $item['name'] = $item['name'] ?? $model->name;
            $item['scene'] = $item['scene'] ?? $model->scene;
            $item['sort_order'] = $item['sort_order'] ?? $model->sort_order;
            $item['updated_by'] = $item['updated_by'] ?? $user->id;

        } else {
            $item['parent_id'] = $item['parent_id'] ?? 0;
            $item['is_leaf_node'] = $item['is_leaf_node'] ?? 0;
            $item['quantification_id'] = $item['quantification_id'] ?? 2;
            $item['category_code'] = $item['category_code'] ?? str_random(60);
            $item['child_select'] = $item['child_select'] ?? 0;
            $item['name'] = $item['name'] ?? '';
            $item['scene'] = $item['scene'] ?? 'sogal';
            $item['sort_order'] = $item['sort_order'] ?? 999;
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

    public function save($item)
    {
        if (isset($item['id']) && !empty($item)) {

            $this->update($item);
        } else {
            $this->add($item);
        }
    }

}