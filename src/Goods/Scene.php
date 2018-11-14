<?php

namespace MrsJoker\Trade\Goods;

use Illuminate\Support\Facades\Cache;
use MrsJoker\Trade\AbstractScene;
use MrsJoker\Trade\Exception\NotSupportedException;

class Scene extends AbstractScene
{
    /**
     * 数据校验
     * @param $item
     * @return \Illuminate\Contracts\Validation\Validator|mixed
     * @throws \MrsJoker\Trade\Exception\NotSupportedException
     */
    protected function validator($item)
    {
        if (isset($item['id']) && !empty($item['id'])) {
            $model = $this->createModel()->findOrFail($data['id']);
            $rules['tree_key'] = [function ($attribute, $value, $fail) use ($model) {
                if ($model->$attribute != $value) {
                    $fail(':attribute can not be change');
                }
            }];

            $find = $model->find($item['id'])->first();

            $rules['goods_code'] = "string|max:100|exists:{$tableName},goods_code,deleted_at,NULL";
        } else {
            $model = $this->createModel();

            $rules['goods_code'] = "required|string|max:100|unique:{$model->getTable()}";
        }


        $rules['goods_name'] = 'required|string|max:255';
        $rules['goods_subname'] = 'string|max:512';
        $rules['sale_num'] = 'numeric|max:99999999';
        $rules['sale_num_virtual'] = 'numeric|max:99999999';
        $rules['is_sale'] = 'required|boolean';
        $rules['viewed'] = 'numeric|max:99999999';
        $rules['buy_limit'] = 'required|numeric|max:99999999';
        $rules['show_sale_time'] = 'required|boolean';
        $rules['sale_start'] = 'date';
        $rules['sale_end'] = 'date';
        $rules['sort_order'] = 'numeric|max:99999999';
        $rules['created_by'] = 'required|exists:users,id,deleted_at,NULL';
        $rules['updated_by'] = 'required|exists:users,id,deleted_at,NULL';


        return \Illuminate\Support\Facades\Validator::make($item, $rules);

    }

    /**
     * @param $item
     * @return bool|mixed
     * @throws NotSupportedException
     */
    public function newItem($item)
    {

        if (empty(app('request')->user())) {
            return app('redirect')->to('login');
        }
        $item['goods_code'] = isset($item['goods_code']) && !empty($item['goods_code']) ? $item['goods_code'] : str_random(60);
        $error = $this->validator($item)->errors()->first();
        if (empty($error)) {
            $model = $this->createModel();

            $model->goods_name = $item['goods_name'];
            $model->goods_subname = $item['goods_subname'];
            $model->goods_code = $item['goods_code'];
            $model->sale_num = $item['sale_num'];
            $model->sale_num_virtual = $item['sale_num_virtual'];
            $model->is_sale = $item['is_sale'];
            $model->viewed = $item['viewed'];
            $model->buy_limit = $item['buy_limit'];
            $model->show_sale_time = $item['show_sale_time'];
            $model->sale_start = $item['sale_start'];
            $model->sale_end = $item['sale_end'];
            $model->sort_order = $item['sort_order'];
            $model->created_by = app('request')->user()->id;
            $model->updated_by = app('request')->user()->id;

            if ($model->save()) {
                Cache::tags($model->getTable())->flush();
                return true;
            }

            throw new NotSupportedException("The server is busy. Please try again later.");
        }
        throw new NotSupportedException($error);
    }

    public function getItem($item)
    {
        // TODO: Implement getItem() method.
    }

    public function getItems($item)
    {
        // TODO: Implement getItems() method.
    }

    public function destoryItem($item)
    {
        // TODO: Implement destoryItem() method.
    }

    /**
     * @param $item
     * @return bool|mixed
     * @throws NotSupportedException
     */
    public function editItem($item)
    {
        if (empty(app('request')->user())) {
            return app('redirect')->to('login');
        }

        if (isset($item['id']) && !empty($item['id'])) {

            $error = $this->validator($item)->errors()->first();
            if (empty($error)) {

                $model = $this->createModel()->find($item['id']);
                $model->goods_name = $item['goods_name'];
                $model->goods_subname = $item['goods_subname'];
                $model->goods_code = $item['goods_code'];
                $model->sale_num = $item['sale_num'];
                $model->sale_num_virtual = $item['sale_num_virtual'];
                $model->is_sale = $item['is_sale'];
                $model->viewed = $item['viewed'];
                $model->buy_limit = $item['buy_limit'];
                $model->show_sale_time = $item['show_sale_time'];
                $model->sale_start = $item['sale_start'];
                $model->sale_end = $item['sale_end'];
                $model->sort_order = $item['sort_order'];
                $model->created_by = app('request')->user()->id;
                $model->updated_by = app('request')->user()->id;

                if ($model->save()) {
                    Cache::tags($model->getTable())->flush();
                    return true;
                }

                throw new NotSupportedException("The server is busy. Please try again later.");
            }
            throw new NotSupportedException($error);
        }
        throw new TreeException("id can not be null.");

//
//        if (isset($item['id']) && !empty($item['id'])) {
//            $error = $this->validator($item)->errors()->first();
//            if (empty($error)) {
//                $model = $this->createModel()->find($item['id']);
//                $model->parent_id = $item['parent_id'];
//                $model->name = $item['name'];
//                if (isset($item['additional_data']) && is_array($item['additional_data'])) {
//                    $model->additional_data = $item['additional_data'];
//                }
//                if (isset($item['order_num'])) {
//                    $model->order_num = $item['order_num'];
//                }
//                if ($model->save()) {
//                    Cache::tags($model->getTable())->flush();
//                    return true;
//                }
//                throw new TreeException("The server is busy. Please try again later.");
//            }
//            throw new TreeException($error);
//        }
//        throw new TreeException("id can not be null.");
    }


}