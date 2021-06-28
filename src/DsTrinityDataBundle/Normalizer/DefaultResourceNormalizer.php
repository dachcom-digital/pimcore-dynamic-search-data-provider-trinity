<?php

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
    /**
     * @var array
     */
    protected $options;

    /**
     * {@inheritdoc}
     */
    public static function configureOptions(OptionsResolver $resolver): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @param ContextDefinitionInterface $contextDefinition
     * @param ResourceContainerInterface $resourceContainer
     *
     * @return array
     */
    protected function normalizeDocument(ContextDefinitionInterface $contextDefinition, ResourceContainerInterface $resourceContainer)
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

    /**
     * @param ContextDefinitionInterface $contextDefinition
     * @param ResourceContainerInterface $resourceContainer
     *
     * @return array
     */
    protected function normalizeAsset(ContextDefinitionInterface $contextDefinition, ResourceContainerInterface $resourceContainer)
    {
        /** @var Asset $asset */
        $asset = $resourceContainer->getResource();

        $documentId = sprintf('%s_%d', 'asset', $asset->getId());
        $resourceMeta = new ResourceMeta($documentId, $asset->getId(), 'asset', $asset->getType(), null, []);
        $returnResourceContainer = $contextDefinition->getContextDispatchType() === ContextDefinitionInterface::CONTEXT_DISPATCH_TYPE_DELETE ? null : $resourceContainer;

        return [new NormalizedDataResource($returnResourceContainer, $resourceMeta)];
    }

    /**
     * @param ContextDefinitionInterface $contextDefinition
     * @param ResourceContainerInterface $resourceContainer
     *
     * @return array
     */
    protected function normalizeDataObject(ContextDefinitionInterface $contextDefinition, ResourceContainerInterface $resourceContainer)
    {
        /** @var DataObject\Concrete $object */
        $object = $resourceContainer->getResource();

        $documentId = sprintf('%s_%d', 'object', $object->getId());
        $resourceMeta = new ResourceMeta($documentId, $object->getId(), 'object', $object->getType(), $object->getClassName(), []);
        $returnResourceContainer = $contextDefinition->getContextDispatchType() === ContextDefinitionInterface::CONTEXT_DISPATCH_TYPE_DELETE ? null : $resourceContainer;

        return [new NormalizedDataResource($returnResourceContainer, $resourceMeta)];
    }
}
