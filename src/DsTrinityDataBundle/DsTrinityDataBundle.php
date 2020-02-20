<?php

namespace DsTrinityDataBundle;

use DsTrinityDataBundle\DependencyInjection\Compiler\DataBuilderPass;
use DsTrinityDataBundle\DependencyInjection\Compiler\ProxyResolverPass;
use DynamicSearchBundle\Provider\Extension\ProviderBundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DsTrinityDataBundle extends Bundle implements ProviderBundleInterface
{
    const PROVIDER_NAME = 'trinity_data';

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DataBuilderPass());
        $container->addCompilerPass(new ProxyResolverPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderName(): string
    {
        return self::PROVIDER_NAME;
    }
}
