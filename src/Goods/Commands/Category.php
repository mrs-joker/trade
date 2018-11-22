<?php

namespace MrsJoker\Trade\Goods\Commands;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use MrsJoker\Trade\Rbac\AbstractCommands;

class Category extends AbstractCommands
{
    /**
     * 栏目数据
     * @param int $goodId
     * @return mixed
     * @throws \MrsJoker\Trade\Exception\NotSupportedException
     */
    public function cachedCategorys(int $goodId)
    {
        $model = $this->createModel()->findOrFail($goodId);
        $tableName = $model->getTable();
        $cacheKey = $this->config['cache_prefix'] . $tableName . $goodId;
        $tag = [
            $tableName,
            $this->config['table_goods_category'],
            $this->createModel(Config::get($this->config['category']))->getTable()
        ];

        return Cache::tags($tag)->remember($cacheKey, 60 * 24 * 30, function () use ($model) {
            return $model->categorys()->get();
        });
    }

    /**
     * goods 数据
     * @param int $categoryId
     * @return mixed
     * @throws \MrsJoker\Trade\Exception\NotSupportedException
     */
    public function cachedGoods(int $categoryId)
    {

        $model = $this->createModel(Config::get($this->config['category']))->findOrFail($categoryId);
        $tableName = $model->getTable();
        $cacheKey = $this->config['cache_prefix'] . $tableName . $categoryId;
        $tag = [
            $tableName,
            $this->config['table_goods_category'],
            $this->createModel()->getTable()
        ];
        return Cache::tags($tag)->remember($cacheKey, 60 * 24 * 30, function () use ($model) {
            return $model->goods()->get();
        });
    }
    public function add($item)
    {
        // TODO: Implement add() method.
    }

    public function update($item)
    {
        // TODO: Implement update() method.
    }

    public function validator($item)
    {
        // TODO: Implement validator() method.
    }

    /**
     * Attach goods to current category.
     * @param int $categoryId
     * @param array $goodIds
     * @throws \MrsJoker\Trade\Exception\NotSupportedException
     */
    public function attachGoods(int $categoryId, array $goodIds)
    {
        $model = $this->createModel(Config::get($this->config['category']))->findOrFail($categoryId);
        $model->goods()->attach($goodIds);
        Cache::tags($this->config['table_goods_category'])->flush();
    }

    /**
     * Attach categorys to current good.
     * @param int $goodId
     * @param array $categoryIds
     * @throws \MrsJoker\Trade\Exception\NotSupportedException
     */
    public function attachCatgorys(int $goodId, array $categoryIds)
    {
        $model = $this->createModel()->findOrFail($goodId);
        $model->categorys()->attach($categoryIds);
        Cache::tags($this->config['table_goods_category'])->flush();
    }


    /**
     * Detach category from current good.
     * @param int $goodId
     * @param array $categoryIds
     * @throws \MrsJoker\Trade\Exception\NotSupportedException
     */
    public function detachCatgory(int $goodId, array $categoryIds)
    {
        $model = $this->createModel()->findOrFail($goodId);
        $model->categorys()->detach($categoryIds);
        Cache::tags($this->config['table_goods_category'])->flush();
    }
    /**
     * Detach all category from current good.
     * @param int $goodId
     * @throws \MrsJoker\Trade\Exception\NotSupportedException
     */
    public function detachCatgorys(int $goodId)
    {
        $catgory = $this->cachedCategorys($goodId);
        if (!$catgory->isEmpty()) {
            $categoryIds = array_pluck($catgory->toArray(), 'id');
            if (!empty($categoryIds) && is_array($categoryIds)) {
                $this->detachPermission($goodId, $categoryIds);
            }
        }
    }

    /**
     * Detach good from current category.
     * @param int $categoryId
     * @param array $goodIds
     * @throws \MrsJoker\Trade\Exception\NotSupportedException
     */
    public function detachGood(int $categoryId, array $goodIds)
    {
        $model = $this->createModel(Config::get($this->config['category']))->findOrFail($categoryId);
        $model->goods()->detach($goodIds);
        Cache::tags($this->config['table_goods_category'])->flush();
    }
    /**
     * Detach all goods from current good.
     * @param int $categoryId
     * @throws \MrsJoker\Trade\Exception\NotSupportedException
     */
    public function detachGoods(int $categoryId)
    {
        $good = $this->cachedGoods($categoryId);
        if (!$good->isEmpty()) {
            $goodIds = array_pluck($good->toArray(), 'id');
            if (!empty($goodIds) && is_array($goodIds)) {
                $this->detachPermission($categoryId, $goodIds);
            }
        }
    }
}