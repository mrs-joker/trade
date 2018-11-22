<?php

namespace MrsJoker\Trade;

use Illuminate\Database\Eloquent\Model;
use MrsJoker\Trade\Exception\NotSupportedException;

abstract class AbstractScene
{
    public $config = [];

    public function init(array $config = [])
    {
        $this->config = $config;
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

//    /**
//     * @param $item
//     * @return mixed
//     */
//    abstract protected function getItem($item);
//
//    /**
//     * @param $item
//     * @return mixed
//     */
//    abstract protected function getItems($item);

    /**
     * @param $item
     * @return mixed
     */
    abstract protected function destory($id);

    /**
     * @param $item
     * @return mixed
     */
    abstract protected function validator($item);

    /**
     * @return mixed
     * @throws NotSupportedException
     */
    public function createModel($m = null)
    {
        $modelName = empty($m) ? $this->config['model'] : $m;
        if (!empty($modelName)) {
            $class = '\\' . ltrim($modelName, '\\');
            $model = new $class;
            if ($model instanceof Model) {
                return $model;
            }
        }
        throw new NotSupportedException("Unknown Scene type.");
    }

    /**
     * Executes named command on given image
     *
     * @param  Image $image
     * @param  string $name
     * @param  array $arguments
     * @return \Intervention\Image\Commands\AbstractCommand
     */
    public function executeCommand($name, $arguments = [])
    {

        $commandName = $this->getCommandClassName($name);
        $command = new $commandName($this->config, $arguments);

        return $command;
    }

    /**
     * Returns classname of given command name
     *
     * @param  string $name
     * @return string
     */
    private function getCommandClassName($name)
    {
        $name = mb_convert_case($name[0], MB_CASE_UPPER, 'utf-8') . mb_substr($name, 1, mb_strlen($name));
        $drivername = $this->getDriverName();

        $classnameLocal = sprintf('\MrsJoker\Trade\%s\Commands\%s', $drivername, ucfirst($name));

        if (class_exists($classnameLocal)) {
            return $classnameLocal;
        }

        throw new NotSupportedException(
            "Command ({$name}) is not available for driver ({$drivername})."
        );

    }

    /**
     * Returns name of current driver instance
     *
     * @return string
     */
    public function getDriverName()
    {
        $reflect = new \ReflectionClass($this);
        $namespace = $reflect->getNamespaceName();

        return substr(strrchr($namespace, "\\"), 1);
    }
}