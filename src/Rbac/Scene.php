<?php
namespace MrsJoker\Trade\Rbac;

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
        return false;
    }

    /**
     * @param $item
     * @return bool|mixed
     * @throws NotSupportedException
     */
    public function newItem($item)
    {
        return false;
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

    }


}