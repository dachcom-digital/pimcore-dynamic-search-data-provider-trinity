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

namespace DsTrinityDataBundle\Registry;

use DsTrinityDataBundle\Service\Builder\DataBuilderInterface;

class DataBuilderRegistry implements DataBuilderRegistryInterface
{
    protected array $builder;

    public function register(DataBuilderInterface $service, string $identifier, string $type): void
    {
        if (!in_array($type, ['document', 'asset', 'object'])) {
            throw new \InvalidArgumentException(
                sprintf('Invalid builder type "%s. Needs to be one of %s.', $type, implode(', ', ['document', 'asset', 'object']))
            );
        }

        if (!in_array(DataBuilderInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), DataBuilderInterface::class, implode(', ', class_implements($service)))
            );
        }

        if (!isset($this->builder[$type])) {
            $this->builder[$type] = [];
        }

        $this->builder[$type][$identifier] = $service;
    }

    public function getByTypeAndIdentifier(string $type, string $identifier): DataBuilderInterface
    {
        return $this->builder[$type][$identifier];
    }
}
