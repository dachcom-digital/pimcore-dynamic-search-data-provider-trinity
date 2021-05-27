<?php

namespace DsTrinityDataBundle\Registry;

use DsTrinityDataBundle\Resource\ProxyResolver\ProxyResolverInterface;

/**
 * @deprecated since 1.0.0 and will be removed in 2.0.0.
 */
class ProxyResolverRegistry implements ProxyResolverRegistryInterface
{
    /**
     * @var array|ProxyResolverInterface[]
     */
    protected $proxyResolver;

    /**
     * @param ProxyResolverInterface $service
     * @param string                 $identifier
     * @param array                  $type
     */
    public function register($service, $identifier, $type)
    {
        if (!is_string($type)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to define a valid builder type.', get_class($service))
            );
        }

        if (!in_array($type, ['document', 'asset', 'object'])) {
            throw new \InvalidArgumentException(
                sprintf('Invalid builder type "%s. Needs to be one of %s.', $type, implode(', ', ['document', 'asset', 'object']))
            );
        }

        if (!in_array(ProxyResolverInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), ProxyResolverInterface::class, implode(', ', class_implements($service)))
            );
        }

        if (!isset($this->proxyResolver[$type])) {
            $this->proxyResolver[$type] = [];
        }

        $this->proxyResolver[$type][$identifier] = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function getByTypeAndIdentifier(string $type, string $identifier)
    {
        return $this->proxyResolver[$type][$identifier];
    }
}
