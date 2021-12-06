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
    public function configureOptions(OptionsResolver $resolver): void;

    public function resolveProxy(ElementInterface $resource, array $proxyOptions, array $contextDefinitionOptions): ?ProxyResourceInterface;
}
