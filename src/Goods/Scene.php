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
    protected function validator($item, $model = null)
    {
        if (isset($item['id']) && !empty($model)) {
            $rules['code'] = "required|string|max:100";
        } else {
            $model = $this->createModel();
            $rules['code'] = "required|string|max:100|unique:{$model->getTable()}";
            $rules['created_by'] = "required|exists:{$this->createModel($this->config['user'])},id,deleted_at,NULL";
        }
        $rules['name'] = 'required|string|max:255';
        $rules['subname'] = 'string|max:512';
        $rules['sale_num'] = 'numeric|max:99999999';
        $rules['sale_num_virtual'] = 'numeric|max:99999999';
        $rules['is_sale'] = 'required|boolean';
        $rules['viewed'] = 'numeric|max:99999999';
        $rules['buy_limit'] = 'required|numeric|max:99999999';
        $rules['show_sale_time'] = 'required|boolean';
        $rules['sale_start'] = 'date';
        $rules['sale_end'] = 'date';
        $rules['sort_order'] = 'numeric|max:99999999';
        $rules['updated_by'] = "required|exists:{$this->createModel($this->config['user'])},id,deleted_at,NULL";

        return \Illuminate\Support\Facades\Validator::make($item, $rules);

    }

    /**
     * 设置默认数据
     * @param $item
     * @param null $model
     * @return mixed
     */
    protected function setDefaultValues($item, $model = null)
    {
        if (empty(app('request')->user())) {
            throw new NotSupportedException("Please login.");
        }
        if (isset($item['id']) && !empty($model)) {
            $item['name'] = $item['name'] ?? $model->name;
            $item['subname'] = $item['subname'] ?? $model->subname;;
            $item['sale_num'] = $item['sale_num'] ?? $model->sale_num;
            $item['sale_num_virtual'] = $item['sale_num_virtual'] ?? $model->sale_num_virtual;
            $item['is_sale'] = $item['is_sale'] ?? $model->is_sale;
            $item['viewed'] = $item['viewed'] ?? $model->viewed;
            $item['buy_limit'] = $item['buy_limit'] ?? $model->buy_limit;
            $item['show_sale_time'] = $item['show_sale_time'] ?? $model->show_sale_time;
            $item['sale_start'] = $item['sale_start'] ?? $model->sale_start;
            $item['sale_end'] = $item['sale_end'] ?? $model->sale_end;
            $item['sort_order'] = $item['sort_order'] ?? $model->sort_order;
            $item['updated_by'] = $item['updated_by'] ?? app('request')->user()->id;
        } else {
            $item['name'] = $item['name'] ?? '';
            $item['code'] = $item['code'] ?? str_random(60);
            $item['subname'] = $item['subname'] ?? '';
            $item['sale_num'] = $item['sale_num'] ?? 0;
            $item['sale_num_virtual'] = $item['sale_num_virtual'] ?? 0;
            $item['is_sale'] = $item['is_sale'] ?? 0;
            $item['viewed'] = $item['viewed'] ?? 0;
            $item['buy_limit'] = $item['buy_limit'] ?? 0;
            $item['show_sale_time'] = $item['show_sale_time'] ?? 0;
            $item['sale_start'] = $item['sale_start'] ?? null;
            $item['sale_end'] = $item['sale_end'] ?? null;
            $item['sort_order'] = $item['sort_order'] ?? 999;
            $item['created_by'] = $item['created_by'] ?? app('request')->user()->id;
            $item['updated_by'] = $item['updated_by'] ?? app('request')->user()->id;
        }
        return $item;
    }

    /**
     * @param $item
     * @return bool|mixed
     * @throws NotSupportedException
     */
    public function add($item)
    {
        $item = $this->setDefaultValues($item);
        $error = $this->validator($item)->errors()->first();
        if (empty($error)) {
            $model = $this->createModel();
            $model->name = $item['name'];
            $model->subname = $item['subname'];
            $model->code = $item['code'];
            $model->sale_num = $item['sale_num'];
            $model->sale_num_virtual = $item['sale_num_virtual'];
            $model->is_sale = $item['is_sale'];
            $model->viewed = $item['viewed'];
            $model->buy_limit = $item['buy_limit'];
            $model->show_sale_time = $item['show_sale_time'];
            $model->sale_start = $item['sale_start'];
            $model->sale_end = $item['sale_end'];
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

    public function getItem($item)
    {
        // TODO: Implement getItem() method.
    }

    public function getItems($item)
    {
        // TODO: Implement getItems() method.
    }

    public function destory($item)
    {
        // TODO: Implement destoryItem() method.
    }

    /**
     * @param $item
     * @return bool|mixed
     * @throws NotSupportedException
     */
    public function update($item)
    {

        if (isset($item['id']) && !empty($item['id'])) {
            $model = $this->createModel()->findOrFail($item['id']);
            $item = $this->setDefaultValues($item, $model);
            $error = $this->validator($item, $model)->errors()->first();
            if (empty($error)) {

                $model->name = $item['name'];
                $model->subname = $item['subname'];
                $model->sale_num = $item['sale_num'];
                $model->sale_num_virtual = $item['sale_num_virtual'];
                $model->is_sale = $item['is_sale'];
                $model->viewed = $item['viewed'];
                $model->buy_limit = $item['buy_limit'];
                $model->show_sale_time = $item['show_sale_time'];
                $model->sale_start = $item['sale_start'];
                $model->sale_end = $item['sale_end'];
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
        throw new TreeException("id can not be null.");
    }


}