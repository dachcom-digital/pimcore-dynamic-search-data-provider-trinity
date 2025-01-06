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

namespace DsTrinityDataBundle\Resource\Scaffolder;

use DynamicSearchBundle\Context\ContextDefinitionInterface;
use DynamicSearchBundle\Resource\ResourceScaffolderInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;

class TrinityDataScaffolder implements ResourceScaffolderInterface
{
    protected ContextDefinitionInterface $contextDefinition;

    public function isBaseResource($resource): bool
    {
        return true;
    }

    public function isApplicable($resource): bool
    {
        if ($resource instanceof Asset) {
            return true;
        } elseif ($resource instanceof Document) {
            return true;
        } elseif ($resource instanceof DataObject\Concrete) {
            return true;
        }

        return false;
    }

    public function setup(ContextDefinitionInterface $contextDefinition, $resource): array
    {
        $this->contextDefinition = $contextDefinition;

        $type = null;
        $dataType = null;

        if ($resource instanceof Asset) {
            $type = 'asset';
            $dataType = $resource->getType();
        } elseif ($resource instanceof Document) {
            $type = 'document';
            $dataType = $resource->getType();
        } elseif ($resource instanceof DataObject\Concrete) {
            $type = 'object';
            $dataType = $resource->getType();
        }

        return [
            'type'      => $type,
            'data_type' => $dataType
        ];
    }
}
