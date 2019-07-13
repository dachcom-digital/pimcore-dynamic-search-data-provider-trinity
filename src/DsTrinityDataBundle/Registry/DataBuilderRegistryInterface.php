<?php

namespace DsTrinityDataBundle\Registry;

use DsTrinityDataBundle\Service\Builder\DataBuilderInterface;

interface DataBuilderRegistryInterface
{
    /**
     * @param string $type
     * @param string $identifier
     *
     * @return DataBuilderInterface
     */
    public function getByTypeAndIdentifier(string $type, string $identifier);
}
