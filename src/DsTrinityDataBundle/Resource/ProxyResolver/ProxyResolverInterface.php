<?php

namespace DsTrinityDataBundle\Resource\ProxyResolver;

use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface ProxyResolverInterface
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver);

    /**
     * @param ElementInterface $resource
     * @param array            $proxyOptions
     *
     * @return ElementInterface
     */
    public function resolveProxy(ElementInterface $resource, array $proxyOptions);
}
