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
use DynamicSearchBundle\Normalizer\Resource\NormalizedDataResource;
use DynamicSearchBundle\Normalizer\Resource\ResourceMeta;
use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DefaultResourceNormalizer extends AbstractResourceNormalizer
{
    protected array $options;

    public static function configureOptions(OptionsResolver $resolver): void
    {
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    protected function normalizeDocument(ContextDefinitionInterface $contextDefinition, ResourceContainerInterface $resourceContainer): array
    {
        /** @var Document $document */
        $document = $resourceContainer->getResource();

        // @todo: Hardlink data detection!
        // @todo: Related document detection! (some content parts could be inherited)
        // @todo: How to handle Snippets?

        $documentId = sprintf('%s_%d', 'document', $document->getId());
        $resourceMeta = new ResourceMeta($documentId, $document->getId(), 'document', $document->getType(), null, []);
        $returnResourceContainer = $contextDefinition->getContextDispatchType() === ContextDefinitionInterface::CONTEXT_DISPATCH_TYPE_DELETE ? null : $resourceContainer;

        return [new NormalizedDataResource($returnResourceContainer, $resourceMeta)];
    }

    protected function normalizeAsset(ContextDefinitionInterface $contextDefinition, ResourceContainerInterface $resourceContainer): array
    {
        /** @var Asset $asset */
        $asset = $resourceContainer->getResource();

        $documentId = sprintf('%s_%d', 'asset', $asset->getId());
        $resourceMeta = new ResourceMeta($documentId, $asset->getId(), 'asset', $asset->getType(), null, []);
        $returnResourceContainer = $contextDefinition->getContextDispatchType() === ContextDefinitionInterface::CONTEXT_DISPATCH_TYPE_DELETE ? null : $resourceContainer;

        return [new NormalizedDataResource($returnResourceContainer, $resourceMeta)];
    }

    protected function normalizeDataObject(ContextDefinitionInterface $contextDefinition, ResourceContainerInterface $resourceContainer): array
    {
        /** @var DataObject\Concrete $object */
        $object = $resourceContainer->getResource();

        $documentId = sprintf('%s_%d', 'object', $object->getId());
        $resourceMeta = new ResourceMeta($documentId, $object->getId(), 'object', $object->getType(), $object->getClassName(), []);
        $returnResourceContainer = $contextDefinition->getContextDispatchType() === ContextDefinitionInterface::CONTEXT_DISPATCH_TYPE_DELETE ? null : $resourceContainer;

        return [new NormalizedDataResource($returnResourceContainer, $resourceMeta)];
    }
}
