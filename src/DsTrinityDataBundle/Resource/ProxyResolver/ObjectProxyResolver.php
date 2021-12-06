<?php

namespace DsTrinityDataBundle\Resource\ProxyResolver;

use DsTrinityDataBundle\DsTrinityDataEvents;
use DsTrinityDataBundle\Event\DataProxyEvent;
use DynamicSearchBundle\Resource\Proxy\ProxyResource;
use DynamicSearchBundle\Resource\Proxy\ProxyResourceInterface;
use Pimcore\Model\DataObject;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated since 1.0.0 and will be removed in 2.0.0.
 */
class ObjectProxyResolver implements ProxyResolverInterface
{
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['fetch_variant_parent_until_reach_object']);
        $resolver->setAllowedTypes('fetch_variant_parent_until_reach_object', ['bool']);
        $resolver->setDefaults([
            'fetch_variant_parent_until_reach_object' => false,
        ]);
    }

    public function resolveProxy(ElementInterface $resource, array $proxyOptions, array $contextDefinitionOptions): ?ProxyResourceInterface
    {
        if (!$resource instanceof DataObject) {
            return null;
        }

        if ($proxyOptions['fetch_variant_parent_until_reach_object'] === false) {
            return null;
        }

        if ($resource->getType() !== DataObject\AbstractObject::OBJECT_TYPE_VARIANT) {
            return null;
        }

        $proxyObject = $resource;
        while ($proxyObject->getType() === DataObject\AbstractObject::OBJECT_TYPE_VARIANT) {
            $proxyObject = $proxyObject->getParent();
        }

        $proxyResource = new ProxyResource($resource, $contextDefinitionOptions['contextDispatchType'], $contextDefinitionOptions['contextName']);
        $proxyResource->setProxyResource($proxyObject);

        $proxyEvent = new DataProxyEvent('object', $proxyResource);
        $this->eventDispatcher->dispatch($proxyEvent, DsTrinityDataEvents::PROXY_DEFAULT_DATA_OBJECT);

        return $proxyEvent->getProxyResource();
    }
}
