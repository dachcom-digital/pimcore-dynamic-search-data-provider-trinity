<?php

namespace DsTrinityDataBundle\Resource\ProxyResolver;

use Pimcore\Model\DataObject;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectProxyResolver implements ProxyResolverInterface
{
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
    public function resolveProxy(ElementInterface $resource, array $proxyOptions)
    {
        if (!$resource instanceof DataObject) {
            return $resource;
        }

        if ($proxyOptions['fetch_variant_parent_until_reach_object'] === false) {
            return $resource;
        }

        if ($resource->getType() !== DataObject\AbstractObject::OBJECT_TYPE_VARIANT) {
            return $resource;
        }

        while ($resource->getType() === DataObject\AbstractObject::OBJECT_TYPE_VARIANT) {
            $resource = $resource->getParent();
        }

        return $resource;
    }
}
