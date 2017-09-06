<?php

namespace NightWatch\Container;

use NightWatch\Container\Instance as Container;

class Manager
{
    /**
     * @var array
     */
    private $instances = [];

    public function get($image)
    {
        if (!isset($this->instances[$image])) {
            $container = (new Container($image))->start();
            $this->instances[$image] = $container;
        }
        return $this->instances[$image];
    }

    public function cleanUp()
    {
        echo 'Clean all docker instances' . "\n";
        echo '====================================' . "\n";
        
        /** @var \NightWatch\Container\Instance $container */
        foreach ($this->instances as $container) {
            $container->stop();
        }
    }
}
