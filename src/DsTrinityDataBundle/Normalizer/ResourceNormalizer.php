<?php

namespace DsTrinityDataBundle\Normalizer;

use DynamicSearchBundle\Context\ContextDataInterface;
use DynamicSearchBundle\Exception\RuntimeException;
use DynamicSearchBundle\Manager\DataManagerInterface;
use DynamicSearchBundle\Manager\TransformerManagerInterface;
use DynamicSearchBundle\Normalizer\Resource\NormalizedDataResource;
use DynamicSearchBundle\Normalizer\ResourceNormalizerInterface;
use DynamicSearchBundle\Transformer\Container\ResourceContainerInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document\Page;
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
        if ($contextData->getContextDispatchType() === ContextDataInterface::CONTEXT_DISPATCH_TYPE_DELETE) {
            return $this->onDeletion($resourceContainer);
        } else {
            return $this->onModification($resourceContainer, $contextData);
        }
    }

    /**
     * @param ResourceContainerInterface $resourceContainer
     *
     * @return array
     */
    protected function onDeletion(ResourceContainerInterface $resourceContainer)
    {
        $resource = $resourceContainer->getResource();

        if (!$resource instanceof ElementInterface) {
            return [];
        }

        if ($resource instanceof Page) {

            // @todo: Hardlink data detection!

            $buildOptions = [];
            if ($this->options['locale_aware_resources'] === true) {
                $documentLocale = $resource->getProperty('language');
                if (empty($documentLocale)) {
                    throw new RuntimeException(sprintf('Cannot determinate locale aware document id "%s": no language property given.', $resource->getId()));
                } else {
                    $buildOptions['locale'] = $documentLocale;
                }
            }

            $resourceId = $this->generateResourceId($resourceContainer, $buildOptions);

            return [new NormalizedDataResource(null, $resourceId, $buildOptions)];
        }

        if ($resource instanceof Asset) {

            $resourceId = $this->generateResourceId($resourceContainer);

            return [new NormalizedDataResource(null, $resourceId)];
        }

        if ($resource instanceof DataObject) {

            $normalizedResources = [];
            if ($this->options['locale_aware_resources'] === true) {
                foreach (\Pimcore\Tool::getValidLanguages() as $language) {
                    $resourceId = $this->generateResourceId($resourceContainer, ['locale' => $language]);
                    $normalizedResources[] = new NormalizedDataResource(null, $resourceId);
                }
            } else {
                $resourceId = $this->generateResourceId($resourceContainer);
                $normalizedResources[] = new NormalizedDataResource(null, $resourceId);
            }

            return $normalizedResources;
        }

        return [];
    }

    /**
     * @param ResourceContainerInterface $resourceContainer
     * @param ContextDataInterface       $contextData
     *
     * @return array
     */
    protected function onModification(ResourceContainerInterface $resourceContainer, ContextDataInterface $contextData)
    {
        $resource = $resourceContainer->getResource();

        if (!$resource instanceof ElementInterface) {
            return [];
        }

        if ($resource instanceof Page) {

            // @todo: Hardlink data detection!

            $buildOptions = [];
            if ($this->options['locale_aware_resources'] === true) {
                $documentLocale = $resource->getProperty('language');
                if (empty($documentLocale)) {
                    throw new RuntimeException(sprintf('Cannot determinate locale aware document id "%s": no language property given.', $resource->getId()));
                } else {
                    $buildOptions['locale'] = $documentLocale;
                }
            }

            $resourceId = $this->generateResourceId($resourceContainer, $buildOptions);

            return [new NormalizedDataResource($resourceContainer, $resourceId, $buildOptions)];
        }

        if ($resource instanceof Asset) {

            $resourceId = $this->generateResourceId($resourceContainer);

            return [new NormalizedDataResource($resourceContainer, $resourceId)];
        }

        if ($resource instanceof DataObject) {

            $normalizedResources = [];
            if ($this->options['locale_aware_resources'] === true) {
                foreach (\Pimcore\Tool::getValidLanguages() as $language) {
                    $resourceId = $this->generateResourceId($resourceContainer, ['locale' => $language]);
                    $normalizedResources[] = new NormalizedDataResource($resourceContainer, $resourceId, ['locale' => $language]);
                }
            } else {
                $resourceId = $this->generateResourceId($resourceContainer);
                $normalizedResources[] = new NormalizedDataResource($resourceContainer, $resourceId);
            }

            return $normalizedResources;
        }

        return [];

    }

    /**
     * @param ResourceContainerInterface $resourceContainer
     * @param array                      $buildOptions
     *
     * @return string|null
     */
    protected function generateResourceId(ResourceContainerInterface $resourceContainer, array $buildOptions = [])
    {
        if (!$resourceContainer->getResource() instanceof ElementInterface) {
            return null;
        }

        $resource = $resourceContainer->getResource();

        $locale = isset($buildOptions['locale']) ? $buildOptions['locale'] : null;
        $documentType = null;
        $id = null;

        if ($resource instanceof DataObject) {
            $documentType = 'object';
            $id = $resource->getId();
        } elseif ($resource instanceof Page) {
            $documentType = 'page';
            $id = $resource->getId();
        }

        if ($documentType === null) {
            return null;
        }

        if ($locale !== null) {
            return sprintf('%s_%s_%d', $documentType, $locale, $id);
        }

        return sprintf('%s_%d', $documentType, $id);
    }
}