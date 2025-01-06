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

namespace DsTrinityDataBundle\Normalizer;

use DynamicSearchBundle\Context\ContextDefinitionInterface;
use DynamicSearchBundle\Manager\DataManagerInterface;
use DynamicSearchBundle\Manager\TransformerManagerInterface;
use DynamicSearchBundle\Normalizer\ResourceNormalizerInterface;
use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Model\Element\ElementInterface;

abstract class AbstractResourceNormalizer implements ResourceNormalizerInterface
{
    protected array $options;

    public function __construct(
        protected TransformerManagerInterface $transformerManager,
        protected DataManagerInterface $dataManager
    ) {
    }

    public function normalizeToResourceStack(ContextDefinitionInterface $contextDefinition, ResourceContainerInterface $resourceContainer): array
    {
        $resource = $resourceContainer->getResource();

        if (!$resource instanceof ElementInterface) {
            return [];
        }

        if ($resource instanceof Document) {
            return $this->normalizeDocument($contextDefinition, $resourceContainer);
        }

        if ($resource instanceof Asset) {
            return $this->normalizeAsset($contextDefinition, $resourceContainer);
        }

        if ($resource instanceof DataObject) {
            return $this->normalizeDataObject($contextDefinition, $resourceContainer);
        }

        return [];
    }

    abstract protected function normalizeDocument(ContextDefinitionInterface $contextDefinition, ResourceContainerInterface $resourceContainer): array;

    abstract protected function normalizeAsset(ContextDefinitionInterface $contextDefinition, ResourceContainerInterface $resourceContainer): array;

    abstract protected function normalizeDataObject(ContextDefinitionInterface $contextDefinition, ResourceContainerInterface $resourceContainer): array;
}
