<?php

namespace DsTrinityDataBundle\Event;

use DynamicSearchBundle\Resource\Proxy\ProxyResourceInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @deprecated since 1.0.0 and will be removed in 2.0.0.
 */
class DataProxyEvent extends Event
{
    /**
     * @var string
     */
    protected $proxyType;

    /**
     * @var ProxyResourceInterface
     */
    protected $proxyResource;

    /**
     * @param string                 $proxyType
     * @param ProxyResourceInterface $proxyResource
     */
    public function __construct(string $proxyType, ProxyResourceInterface $proxyResource)
    {
        $this->proxyType = $proxyType;
        $this->proxyResource = $proxyResource;
    }

    /**
     * @return string
     */
    public function getProxyType()
    {
        return $this->proxyType;
    }

    /**
     * @return ProxyResourceInterface
     */
    public function getProxyResource()
    {
        return $this->proxyResource;
    }
}
