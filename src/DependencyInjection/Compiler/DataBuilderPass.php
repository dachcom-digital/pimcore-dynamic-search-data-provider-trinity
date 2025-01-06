<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

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
