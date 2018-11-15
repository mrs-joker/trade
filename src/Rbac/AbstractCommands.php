<?php

namespace MrsJoker\Trade\Rbac;

use Illuminate\Database\Eloquent\Model;
use MrsJoker\Trade\Exception\NotSupportedException;

abstract class AbstractCommands
{
    protected $config = [];

    protected $arguments = [];

    public function __construct($config = [], $arguments = [])
    {

        if (empty(app('request')->user())) {
            throw new NotSupportedException("Please login.");
        }
        $this->config = $config;
        $this->arguments = $arguments;
    }

    /**
     * @param $item
     * @return mixed
     */
    abstract protected function add($item);

    /**
     * @param $item
     * @return mixed
     */
    abstract protected function update($item);

    /**
     * @param $item
     * @return mixed
     */
    abstract protected function validator($item);

    /**
     * @return mixed
     * @throws NotSupportedException
     */
    public function createModel($model = null)
    {
        if (isset($model)) {
            $class = '\\' . ltrim($model, '\\');
            $model = new $class;
            if ($model instanceof Model) {
                return $model;
            }
        }
        throw new NotSupportedException("Unknown Scene type.");
    }
}