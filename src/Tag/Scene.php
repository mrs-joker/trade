<?php
namespace MrsJoker\Trade\Tag;


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
        $rules['name'] = "required|string|max:100";
        if (isset($item['id']) && !empty($model)) {
            $rules['created_by'] = "required|exists:{$this->createModel($this->config['user'])->getTable()},id,deleted_at,NULL";
        } else {
        }
        $rules['scene'] = [function ($attribute, $value, $fail) {
            if (!isset($this->config['scene'][$value])) {
                $fail(':attribute can not be change');
            }
        }];
        $rules['directional'] = [function ($attribute, $value, $fail) {
            if (!isset($this->config['directional'][$value])) {
                $fail(':attribute can not be change');
            }
        }];
        $rules['rules'] = "array";
        $rules['updated_by'] = "required|exists:{$this->createModel($this->config['user'])->getTable()},id,deleted_at,NULL";

        return \Illuminate\Support\Facades\Validator::make($item, $rules);
    }

    /**
     * 设置默认数据
     * @param $item
     * @param null $model
     * @return mixed
     */
    public function setDefaultValues($item, $model = null)
    {
        if (empty(app('request')->user())) {
            throw new NotSupportedException("Please login.");
        }
        if (isset($item['id']) && !empty($model)) {
            $item['name'] = $item['name'] ?? $model->name;
            $item['scene'] = $item['scene'] ?? $model->scene;
            $item['directional'] = $item['directional'] ?? $model->scene;
            $item['rules'] = $item['rules'] ?? $model->rules;
            $item['character_equal'] = $item['character_equal'] ?? $model->character_equal;
            $item['character_like'] = $item['character_like'] ?? $model->character_like;
            $item['character_collect'] = $item['character_collect'] ?? $model->character_collect;
            $item['number_range'] = $item['number_range'] ?? $model->number_range;
            $item['updated_by'] = $item['updated_by'] ?? app('request')->user()->id;
        } else {
            $item['name'] = $item['name'] ?? '';
            $item['scene'] = $item['scene'] ?? 'sogal';
            $item['directional'] = $item['directional'] ?? 'region';
            $item['rules'] = $item['rules'] ?? [];
            $item['character_equal'] = $item['character_equal'] ?? '';
            $item['character_like'] = $item['character_like'] ?? '';
            $item['character_collect'] = $item['character_collect'] ?? [];
            $item['number_range'] = $item['number_range'] ?? [];
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

            $model->name = $item['name'] ?? '';
            $model->scene = $item['scene'] ?? 'sogal';
            $model->directional = $item['directional'] ?? '';
            $model->rules = $item['rules'] ?? [];
            $model->character_equal = $item['character_equal'] ?? '';
            $model->character_like = $item['character_like'] ?? '';
            $model->character_collect = $item['character_collect'] ?? [];
            $model->number_range = $item['number_range'] ?? [];
            $model->created_by = $item['created_by'] ?? app('request')->user()->id;
            $model->updated_by = $item['updated_by'] ?? app('request')->user()->id;

            if ($model->save()) {
                Cache::tags($model->getTable())->flush();
                return true;
            }

            throw new NotSupportedException("The server is busy. Please try again later.");
        }
        throw new NotSupportedException($error);
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

                $model->name = $item['name'] ?? '';
                $model->scene = $item['scene'] ?? 'sogal';
                $model->directional = $item['directional'] ?? '';
                $model->rules = $item['rules'] ?? [];
                $model->character_equal = $item['character_equal'] ?? '';
                $model->character_like = $item['character_like'] ?? '';
                $model->character_collect = $item['character_collect'] ?? [];
                $model->number_range = $item['number_range'] ?? [];
                $model->updated_by = $item['updated_by'] ?? app('request')->user()->id;

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

    public function save($item)
    {
        if (isset($item['id']) && !empty($item)) {
            $this->update($item);
        } else {
            $this->add($item);
        }
    }


}