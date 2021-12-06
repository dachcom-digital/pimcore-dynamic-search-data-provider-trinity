<?php

namespace DsTrinityDataBundle\Configuration;

interface ConfigurationInterface
{
    public function get(string $slot): mixed;
}
