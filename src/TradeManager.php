<?php

namespace MrsJoker\Trade;

use Closure;
use MrsJoker\Trade\Exception\NotSupportedException;
use MrsJoker\Trade\Rbac\Scene;

class TradeManager
{
    /**
     * Config
     *
     * @var array
     */
    public $config = [];

    /**
     * Creates new instance of Trade Manager
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->configure($config);
    }

    /**
     * Overrides configuration settings
     *
     * @param array $config
     *
     * @return self
     */
    public function configure(array $config = [])
    {
        $this->config = array_replace($this->config, $config);
    }

    /**
     * Initiates an Trade instance from different input types
     *
     * @param  mixed $data
     *
     * @return \Intervention\Image\Image
     */
    public function make($scene)
    {
        return $this->createScene($scene);
    }

    public function rootPath($path = null)
    {
        return env('APP_URL', '') . '/public/' . $path;
    }

    /**
     * Creates a scene instance according to config settings
     */
    private function createScene($scene)
    {

        if (is_string($scene)) {

            $sceneClass = sprintf('MrsJoker\\Trade\\%s\\Scene', ucfirst($scene));
            if (isset($this->config[$scene]) && class_exists($sceneClass)) {
                $sceneModel = new $sceneClass;
                $sceneModel->init($this->config[$scene]);
                return $sceneModel;
            } elseif (array_has($this->config, $scene)) {
                $sceneData = explode('.', $scene);
                $sceneClass = sprintf('MrsJoker\\Trade\\%s\\Scene', ucfirst($sceneData[0]));
                if (class_exists($sceneClass)) {
                    $sceneModel = new $sceneClass;
                    $sceneModel->init(data_get($this->config, $scene, []));
                    return $sceneModel;
                }
            }
            throw new NotSupportedException(
                "Scene ({$scene}) could not be instantiated."
            );
        }

        if ($scene instanceof AbstractScene) {
            return new $scene;
        }

        throw new NotSupportedException("Unknown Scene type.");
    }

}
