<?php

namespace DsTrinityDataBundle\Configuration;

interface ConfigurationInterface
{
    /**
     * @param string $slot
     *
     * @return mixed
     */
    public function get($slot);
}
