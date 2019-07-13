<?php

namespace DsTrinityDataBundle\Normalizer;

use DynamicSearchBundle\Context\ContextDataInterface;
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
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param ContextDataInterface       $contextData
     * @param ResourceContainerInterface $resourceContainer
     *
     * @return array
     */
    protected function normalizeDocument(ContextDataInterface $contextData, ResourceContainerInterface $resourceContainer)
    {
        /** @var Document $document */
        $document = $resourceContainer->getResource();

        if (!method_exists($document, 'getProperty')) {
            return [];
        }

        // @todo: Related document detection! (some content parts could be inherited)

        $documentId = sprintf('%s_%d', 'document', $document->getId());
        $resourceMeta = new ResourceMeta($documentId, $document->getId(), 'document', $document->getType());
        $returnResourceContainer = $contextData->getContextDispatchType() === ContextDataInterface::CONTEXT_DISPATCH_TYPE_DELETE ? null : $resourceContainer;

        return [new NormalizedDataResource($returnResourceContainer, $resourceMeta)];

    }

    /**
     * @param ContextDataInterface       $contextData
     * @param ResourceContainerInterface $resourceContainer
     *
     * @return array
     */
    protected function normalizeAsset(ContextDataInterface $contextData, ResourceContainerInterface $resourceContainer)
    {
        /** @var Asset $asset */
        $asset = $resourceContainer->getResource();

        $documentId = sprintf('%s_%d', 'asset', $asset->getId());
        $resourceMeta = new ResourceMeta($documentId, $asset->getId(), 'asset', $asset->getType());
        $returnResourceContainer = $contextData->getContextDispatchType() === ContextDataInterface::CONTEXT_DISPATCH_TYPE_DELETE ? null : $resourceContainer;

        return [new NormalizedDataResource($returnResourceContainer, $resourceMeta)];
    }

    /**
     * @param ContextDataInterface       $contextData
     * @param ResourceContainerInterface $resourceContainer
     *
     * @return array
     */
    protected function normalizeDataObject(ContextDataInterface $contextData, ResourceContainerInterface $resourceContainer)
    {
        /** @var DataObject\Concrete $object */
        $object = $resourceContainer->getResource();

        $documentId = sprintf('%s_%d', 'object', $object->getId());
        $resourceMeta = new ResourceMeta($documentId, $object->getId(), 'object', $object->getType());
        $returnResourceContainer = $contextData->getContextDispatchType() === ContextDataInterface::CONTEXT_DISPATCH_TYPE_DELETE ? null : $resourceContainer;

        return [new NormalizedDataResource($returnResourceContainer, $resourceMeta)];
    }
}