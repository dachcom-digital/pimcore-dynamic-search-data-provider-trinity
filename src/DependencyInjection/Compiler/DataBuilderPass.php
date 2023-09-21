<?php

namespace DsTrinityDataBundle\DependencyInjection\Compiler;

use DsTrinityDataBundle\Registry\DataBuilderRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class DataBuilderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds('ds_trinity_data.data_builder', true) as $id => $tags) {
            $definition = $container->getDefinition(DataBuilderRegistry::class);
            foreach ($tags as $attributes) {
                $definition->addMethodCall('register', [new Reference($id), $attributes['identifier'], $attributes['type']]);
            }
        }
    }
}
