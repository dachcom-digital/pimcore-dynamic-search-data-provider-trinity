<?php

namespace DsTrinityDataBundle\Resource\ProxyResolver;

use DynamicSearchBundle\Resource\Proxy\ProxyResourceInterface;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated since 1.0.0 and will be removed in 2.0.0.
 */
interface ProxyResolverInterface
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver);

    /**
     * @param ElementInterface $resource
     * @param array            $proxyOptions
     * @param array            $contextDefinitionOptions
     *
     * @return ProxyResourceInterface|null
     */
    public function resolveProxy(ElementInterface $resource, array $proxyOptions, array $contextDefinitionOptions);
}
