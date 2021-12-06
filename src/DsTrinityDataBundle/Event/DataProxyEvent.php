<?php

namespace DsTrinityDataBundle\Event;

use DynamicSearchBundle\Resource\Proxy\ProxyResourceInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @deprecated since 1.0.0 and will be removed in 2.0.0.
 */
class DataProxyEvent extends Event
{
    protected string $proxyType;
    protected ProxyResourceInterface $proxyResource;

    public function __construct(string $proxyType, ProxyResourceInterface $proxyResource)
    {
        $this->proxyType = $proxyType;
        $this->proxyResource = $proxyResource;
    }

    public function getProxyType(): string
    {
        return $this->proxyType;
    }

    public function getProxyResource(): ProxyResourceInterface
    {
        return $this->proxyResource;
    }
}
