<?php

namespace DsTrinityDataBundle;

use DsTrinityDataBundle\DependencyInjection\Compiler\DataBuilderPass;
use DynamicSearchBundle\Provider\Extension\ProviderBundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DsTrinityDataBundle extends Bundle implements ProviderBundleInterface
{
    public const PROVIDER_NAME = 'trinity_data';

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new DataBuilderPass());
    }

    public function getProviderName(): string
    {
        return self::PROVIDER_NAME;
    }
}
