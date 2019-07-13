<?php

namespace DsTrinityDataBundle\Normalizer;

use DynamicSearchBundle\Context\ContextDataInterface;
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
    protected abstract function normalizeDocument(ContextDataInterface $contextData, ResourceContainerInterface $resourceContainer);

    /**
     * @param ContextDataInterface       $contextData
     * @param ResourceContainerInterface $resourceContainer
     *
     * @return array
     */
    protected abstract function normalizeAsset(ContextDataInterface $contextData, ResourceContainerInterface $resourceContainer);

    /**
     * @param ContextDataInterface       $contextData
     * @param ResourceContainerInterface $resourceContainer
     *
     * @return array
     */
    protected abstract function normalizeDataObject(ContextDataInterface $contextData, ResourceContainerInterface $resourceContainer);

}