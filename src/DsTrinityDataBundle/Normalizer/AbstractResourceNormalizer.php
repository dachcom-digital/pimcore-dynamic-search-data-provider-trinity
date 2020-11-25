<?php

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
     * {@inheritdoc}
     */
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

    /**
     * @param ContextDefinitionInterface $contextDefinition
     * @param ResourceContainerInterface $resourceContainer
     *
     * @return array
     */
    abstract protected function normalizeDocument(ContextDefinitionInterface $contextDefinition, ResourceContainerInterface $resourceContainer);

    /**
     * @param ContextDefinitionInterface $contextDefinition
     * @param ResourceContainerInterface $resourceContainer
     *
     * @return array
     */
    abstract protected function normalizeAsset(ContextDefinitionInterface $contextDefinition, ResourceContainerInterface $resourceContainer);

    /**
     * @param ContextDefinitionInterface $contextDefinition
     * @param ResourceContainerInterface $resourceContainer
     *
     * @return array
     */
    abstract protected function normalizeDataObject(ContextDefinitionInterface $contextDefinition, ResourceContainerInterface $resourceContainer);
}
