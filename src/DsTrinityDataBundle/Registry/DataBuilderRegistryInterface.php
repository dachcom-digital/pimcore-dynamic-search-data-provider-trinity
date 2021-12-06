<?php

namespace DsTrinityDataBundle\Registry;

use DsTrinityDataBundle\Service\Builder\DataBuilderInterface;

interface DataBuilderRegistryInterface
{
    public function getByTypeAndIdentifier(string $type, string $identifier): DataBuilderInterface;
}
