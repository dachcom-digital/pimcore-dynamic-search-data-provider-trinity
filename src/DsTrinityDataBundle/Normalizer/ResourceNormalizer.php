<?php

namespace DsTrinityDataBundle\Normalizer;

use DynamicSearchBundle\Context\ContextDataInterface;
use DynamicSearchBundle\Exception\RuntimeException;
use DynamicSearchBundle\Manager\DataManagerInterface;
use DynamicSearchBundle\Manager\TransformerManagerInterface;
use DynamicSearchBundle\Normalizer\Resource\NormalizedDataResource;
use DynamicSearchBundle\Normalizer\Resource\ResourceMeta;
use DynamicSearchBundle\Normalizer\Resource\ResourceMetaInterface;
use DynamicSearchBundle\Normalizer\ResourceNormalizerInterface;
use DynamicSearchBundle\Transformer\Container\ResourceContainerInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceNormalizer implements ResourceNormalizerInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var TransformerManagerInterface
     */
    protected $transformerManager;

    /**
     * @var DataManagerInterface
     */
    protected $dataManager;

    /**
     * @param TransformerManagerInterface $transformerManager
     * @param DataManagerInterface        $dataManager
     */
    public function __construct(
        TransformerManagerInterface $transformerManager,
        DataManagerInterface $dataManager
    ) {
        $this->transformerManager = $transformerManager;
        $this->dataManager = $dataManager;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['locale_aware_resources' => false]);
        $resolver->setRequired('locale_aware_resources');
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function normalizeToResourceStack(ContextDataInterface $contextData, ResourceContainerInterface $resourceContainer): array
    {
        $resource = $resourceContainer->getResource();

        if (!$resource instanceof ElementInterface) {
            return [];
        }

        if ($resource instanceof Document) {
            return $this->normalizeDocument($contextData, $resourceContainer);
        }

        if ($resource instanceof Asset) {
            return $this->normalizeAsset($contextData, $resourceContainer);
        }

        if ($resource instanceof DataObject) {
            return $this->normalizeDataObject($contextData, $resourceContainer);
        }

        return [];

    }

    /**
     * @param ContextDataInterface       $contextData
     * @param ResourceContainerInterface $resourceContainer
     *
     * @return array
     */
    protected function normalizeDocument(ContextDataInterface $contextData, ResourceContainerInterface $resourceContainer)
    {
        $resource = $resourceContainer->getResource();

        if (!method_exists($resource, 'getProperty')) {
            return [];
        }

        // @todo: Hardlink data detection!
        // @todo: Related document detection! (some content parts could be inherited)

        $buildOptions = [];
        if ($this->options['locale_aware_resources'] === true) {
            $documentLocale = $resource->getProperty('language');
            if (empty($documentLocale)) {
                throw new RuntimeException(sprintf('Cannot determinate locale aware document id "%s": no language property given.', $resource->getId()));
            } else {
                $buildOptions['locale'] = $documentLocale;
            }
        }

        $resource = $this->generateDataResource($resourceContainer, $buildOptions);

        if ($resource === null) {
            return [];
        }

        return [$resource];
    }

    /**
     * @param ContextDataInterface       $contextData
     * @param ResourceContainerInterface $resourceContainer
     *
     * @return array
     */
    protected function normalizeAsset(ContextDataInterface $contextData, ResourceContainerInterface $resourceContainer)
    {
        $resource = $this->generateDataResource($resourceContainer);;
        if ($resource === null) {
            return [];
        }

        return [$resource];
    }

    /**
     * @param ContextDataInterface       $contextData
     * @param ResourceContainerInterface $resourceContainer
     *
     * @return array
     */
    protected function normalizeDataObject(ContextDataInterface $contextData, ResourceContainerInterface $resourceContainer)
    {
        $normalizedResources = [];
        if ($this->options['locale_aware_resources'] === true) {
            foreach (\Pimcore\Tool::getValidLanguages() as $language) {
                $resource = $this->generateDataResource($resourceContainer, ['locale' => $language]);
                if ($resource !== null) {
                    $normalizedResources[] = $resource;
                }
            }
        } else {
            $resource = $this->generateDataResource($resourceContainer);
            if ($resource !== null) {
                $normalizedResources[] = $resource;
            }
        }

        return $normalizedResources;
    }

    /**
     * @param ResourceContainerInterface $resourceContainer
     * @param array                      $buildOptions
     *
     * @return NormalizedDataResource|null
     */
    protected function generateDataResource(ResourceContainerInterface $resourceContainer, $buildOptions = [])
    {
        $resourceMeta = $this->generateResourceMeta($resourceContainer, $buildOptions);

        if ($resourceMeta === null) {
            return null;
        }

        return new NormalizedDataResource(
            $resourceContainer,
            $resourceMeta,
            $buildOptions
        );
    }

    /**
     * @param ResourceContainerInterface $resourceContainer
     * @param array                      $buildOptions
     *
     * @return ResourceMetaInterface|null
     */
    protected function generateResourceMeta(ResourceContainerInterface $resourceContainer, array $buildOptions = [])
    {
        if (!$resourceContainer->getResource() instanceof ElementInterface) {
            return null;
        }

        $resource = $resourceContainer->getResource();

        $locale = isset($buildOptions['locale']) ? $buildOptions['locale'] : null;

        $documentId = null;
        $resourceId = null;
        $resourceCollectionType = null;
        $resourceType = null;

        if ($resource instanceof DataObject) {
            $resourceCollectionType = 'object';
            $resourceType = $resource->getType();
            $resourceId = $resource->getId();
        } elseif ($resource instanceof Asset) {
            $resourceCollectionType = 'asset';
            $resourceType = $resource->getType();
            $resourceId = $resource->getId();
        } elseif ($resource instanceof Document) {
            $resourceCollectionType = 'document';
            $resourceType = $resource->getType();
            $resourceId = $resource->getId();
        }

        if ($resourceCollectionType === null) {
            return null;
        }

        if ($locale !== null) {
            $documentId = sprintf('%s_%s_%d', $resourceCollectionType, $locale, $resourceId);
        } else {
            $documentId = sprintf('%s_%d', $resourceCollectionType, $resourceId);
        }

        return new ResourceMeta($documentId, $resourceId, $resourceCollectionType, $resourceType);
    }
}