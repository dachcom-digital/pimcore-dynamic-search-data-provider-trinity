<?php

namespace DsTrinityDataBundle\DependencyInjection\Compiler;

use DsTrinityDataBundle\Registry\ProxyResolverRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @deprecated since 1.0.0 and will be removed in 2.0.0.
 */
final class ProxyResolverPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds('ds_trinity_data.proxy_resolver', true) as $id => $tags) {
            $definition = $container->getDefinition(ProxyResolverRegistry::class);
            foreach ($tags as $attributes) {
                $definition->addMethodCall('register', [new Reference($id), $attributes['identifier'], $attributes['type']]);
            }
        }
    }
}
