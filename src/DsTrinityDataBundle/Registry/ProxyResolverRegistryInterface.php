<?php

namespace DsTrinityDataBundle\Registry;

use DsTrinityDataBundle\Resource\ProxyResolver\ProxyResolverInterface;

interface ProxyResolverRegistryInterface
{
    /**
     * @param string $type
     * @param string $identifier
     *
     * @return ProxyResolverInterface
     */
    public function getByTypeAndIdentifier(string $type, string $identifier);
}
