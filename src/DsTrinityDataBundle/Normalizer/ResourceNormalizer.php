<?php

namespace DsTrinityDataBundle\Normalizer;

use DynamicSearchBundle\Context\ContextDataInterface;
use DynamicSearchBundle\Exception\RuntimeException;
use DynamicSearchBundle\Manager\DataManagerInterface;
use DynamicSearchBundle\Manager\TransformerManagerInterface;
use DynamicSearchBundle\Normalizer\Resource\NormalizedDataResource;
use DynamicSearchBundle\Normalizer\ResourceIdBuilderInterface;
use DynamicSearchBundle\Normalizer\ResourceNormalizerInterface;
use DynamicSearchBundle\Transformer\Container\DocumentContainerInterface;
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
     * @var ResourceIdBuilderInterface
     */
    protected $resourceIdBuilder;

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
    public function setIdBuilder(ResourceIdBuilderInterface $resourceIdBuilder)
    {
        $this->resourceIdBuilder = $resourceIdBuilder;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdBuilder()
    {
        return $this->resourceIdBuilder;
    }

    /**
     * {@inheritDoc}
     */
    public function normalizeToResourceStack(ContextDataInterface $contextData, DocumentContainerInterface $documentContainer): array
    {
        if ($contextData->getContextDispatchType() === ContextDataInterface::CONTEXT_DISPATCH_TYPE_DELETE) {
            return $this->onDeletion($documentContainer);
        } else {
            return $this->onModification($documentContainer, $contextData);
        }
    }

    /**
     * @param DocumentContainerInterface $documentContainer
     *
     * @return array
     */
    protected function onDeletion(DocumentContainerInterface $documentContainer)
    {
        $resource = $documentContainer->getResource();

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

            $resourceId = $this->resourceIdBuilder->build($documentContainer, $buildOptions);

            return [new NormalizedDataResource(null, $resourceId, $buildOptions)];
        }

        if ($resource instanceof Asset) {

            $resourceId = $this->resourceIdBuilder->build($documentContainer);

            return [new NormalizedDataResource(null, $resourceId)];
        }

        if ($resource instanceof DataObject) {

            $normalizedResources = [];
            if ($this->options['locale_aware_resources'] === true) {
                foreach (\Pimcore\Tool::getValidLanguages() as $language) {
                    $resourceId = $this->resourceIdBuilder->build($documentContainer, ['locale' => $language]);
                    $normalizedResources[] = new NormalizedDataResource(null, $resourceId);
                }
            } else {
                $resourceId = $this->resourceIdBuilder->build($documentContainer);
                $normalizedResources[] = new NormalizedDataResource(null, $resourceId);
            }

            return $normalizedResources;
        }

        return [];
    }

    /**
     * @param DocumentContainerInterface $documentContainer
     * @param ContextDataInterface       $contextData
     *
     * @return array
     */
    protected function onModification(DocumentContainerInterface $documentContainer, ContextDataInterface $contextData)
    {
        $resource = $documentContainer->getResource();

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

            $resourceId = $this->resourceIdBuilder->build($documentContainer, $buildOptions);

            return [new NormalizedDataResource($documentContainer, $resourceId, $buildOptions)];
        }

        if ($resource instanceof Asset) {

            $resourceId = $this->resourceIdBuilder->build($documentContainer);

            return [new NormalizedDataResource($documentContainer, $resourceId)];
        }

        if ($resource instanceof DataObject) {

            $normalizedResources = [];
            if ($this->options['locale_aware_resources'] === true) {
                foreach (\Pimcore\Tool::getValidLanguages() as $language) {
                    $resourceId = $this->resourceIdBuilder->build($documentContainer, ['locale' => $language]);
                    $normalizedResources[] = new NormalizedDataResource($documentContainer, $resourceId, ['locale' => $language]);
                }
            } else {
                $resourceId = $this->resourceIdBuilder->build($documentContainer);
                $normalizedResources[] = new NormalizedDataResource($documentContainer, $resourceId);
            }

            return $normalizedResources;
        }

        return [];

    }
}