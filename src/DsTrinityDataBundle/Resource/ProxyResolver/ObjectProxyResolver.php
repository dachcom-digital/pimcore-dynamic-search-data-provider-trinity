<?php

namespace DsTrinityDataBundle\Resource\ProxyResolver;

use DsTrinityDataBundle\DsTrinityDataEvents;
use DsTrinityDataBundle\Event\DataProxyEvent;
use DynamicSearchBundle\Resource\Proxy\ProxyResource;
use Pimcore\Model\DataObject;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectProxyResolver implements ProxyResolverInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['fetch_variant_parent_until_reach_object']);
        $resolver->setAllowedTypes('fetch_variant_parent_until_reach_object', ['bool']);
        $resolver->setDefaults([
            'fetch_variant_parent_until_reach_object' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function resolveProxy(ElementInterface $resource, array $proxyOptions, array $contextDataOptions)
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
            $proxyObject = $resource->getParent();
        }

        $proxyResource = new ProxyResource($resource, $contextDataOptions['contextDispatchType'], $contextDataOptions['contextName']);
        $proxyResource->setProxyResource($proxyObject);

        $proxyEvent = new DataProxyEvent('object', $proxyResource);
        $this->eventDispatcher->dispatch(DsTrinityDataEvents::PROXY_DEFAULT_DATA_OBJECT, $proxyEvent);

        return $proxyEvent->getProxyResource();
    }
}
