<?php

namespace DsTrinityDataBundle\Registry;

use DsTrinityDataBundle\Resource\ProxyResolver\ProxyResolverInterface;

/**
 * @deprecated since 1.0.0 and will be removed in 2.0.0.
 */
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
