<?php

namespace ExtSession\Processors\Mgr\Traits;

trait BaseTrait
{
    public function getBooleanProperty($k, $default = null)
    {
        return ($this->getProperty($k, $default) === 'true' || $this->getProperty($k, $default) === true || $this->getProperty($k, $default) === '1' || $this->getProperty($k, $default) === 1);
    }

    public function getJsonProperty($k, $default = null)
    {
        if ($value = $this->getProperty($k, $default)) {
            return json_decode($value, true);
        }

        return [];
    }

}