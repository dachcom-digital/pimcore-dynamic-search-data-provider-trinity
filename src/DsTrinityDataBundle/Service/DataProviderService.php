<?php

namespace DsTrinityDataBundle\Service;

use DsTrinityDataBundle\DsTrinityDataBundle;
use DsTrinityDataBundle\Registry\DataBuilderRegistryInterface;
use DsTrinityDataBundle\Service\Builder\DataBuilderInterface;
use DynamicSearchBundle\DynamicSearchEvents;
use DynamicSearchBundle\Event\NewDataEvent;
use DynamicSearchBundle\Logger\LoggerInterface;
use DynamicSearchBundle\Normalizer\Resource\ResourceMetaInterface;
use DynamicSearchBundle\Provider\DataProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DataProviderService implements DataProviderServiceInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DataBuilderRegistryInterface
     */
    protected $dataBuilderRegistry;

    /**
     * @var string
     */
    protected $contextName;

    /**
     * @var string
     */
    protected $contextDispatchType;

    /**
     * @var array
     */
    protected $indexOptions;

    /**
     * @param LoggerInterface              $logger
     * @param EventDispatcherInterface     $eventDispatcher
     * @param DataBuilderRegistryInterface $dataBuilderRegistry
     */
    public function __construct(
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        DataBuilderRegistryInterface $dataBuilderRegistry
    ) {
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->dataBuilderRegistry = $dataBuilderRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function setContextName(string $contextName)
    {
        $this->contextName = $contextName;
    }

    /**
     * {@inheritDoc}
     */
    public function setContextDispatchType(string $dispatchType)
    {
        $this->contextDispatchType = $dispatchType;
    }

    /**
     * {@inheritDoc}
     */
    public function setIndexOptions(array $indexOptions)
    {
        $this->indexOptions = $indexOptions;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchListData()
    {
        foreach (['asset', 'document', 'object'] as $type) {
            $this->fetchByType($type, DataProviderInterface::PROVIDER_BEHAVIOUR_FULL_DISPATCH);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function fetchSingleData(ResourceMetaInterface $resourceMeta)
    {
        $elementType = $resourceMeta->getResourceType();
        $elementId = $resourceMeta->getResourceId();

        if (!in_array($elementType, ['asset', 'document', 'object'])) {
            $this->log('error', sprintf('cannot insert data from identifier "%s". wrong type "%s" given', $elementId, $elementType));
            return;
        }

        $this->fetchByTypeAndId($elementType, DataProviderInterface::PROVIDER_BEHAVIOUR_SINGLE_DISPATCH, $elementId, $resourceMeta);

    }

    /**
     * @param string $type
     * @param string $providerBehaviour
     */
    protected function fetchByType(string $type, string $providerBehaviour)
    {
        $builder = null;

        if ($this->indexOptions[sprintf('index_%s', $type)] === false) {
            return;
        }

        $builderIdentifier = sprintf('%s_data_builder_identifier', $type);
        $builder = $this->dataBuilderRegistry->getByTypeAndIdentifier($type, $this->indexOptions[$builderIdentifier]);

        if (!$builder instanceof DataBuilderInterface) {
            $this->log('error', sprintf('could not resolve data builder for type "%s"', $type));
            return;
        }

        $options = $this->getTypeOptions($type);
        $elements = $builder->buildByList($options);

        $this->dispatchData($elements, $providerBehaviour);
    }

    /**
     * @param string                $type
     * @param string                $providerBehaviour
     * @param int                   $id
     * @param ResourceMetaInterface $resourceMeta
     */
    protected function fetchByTypeAndId(string $type, string $providerBehaviour, $id, ?ResourceMetaInterface $resourceMeta)
    {
        $builder = null;

        if ($this->indexOptions[sprintf('index_%s', $type)] === false) {
            return;
        }

        $builderIdentifier = sprintf('%s_data_builder_identifier', $type);
        $builder = $this->dataBuilderRegistry->getByTypeAndIdentifier($type, $this->indexOptions[$builderIdentifier]);

        if (!$builder instanceof DataBuilderInterface) {
            $this->log('error', sprintf('could not resolve data builder for type "%s"', $type));
            return;
        }

        $element = $builder->buildById((int) $id);

        $this->dispatchData([$element], $providerBehaviour, $resourceMeta);
    }

    /**
     * @param string $level
     * @param string $message
     */
    protected function log($level, $message)
    {
        $this->logger->log($level, $message, DsTrinityDataBundle::PROVIDER_NAME, $this->contextName);
    }

    /**
     * @param array                      $elements
     * @param string                     $providerBehaviour
     * @param ResourceMetaInterface|null $resourceMeta
     */
    protected function dispatchData(array $elements, string $providerBehaviour, ?ResourceMetaInterface $resourceMeta = null)
    {
        foreach ($elements as $element) {
            $newDataEvent = new NewDataEvent($this->contextDispatchType, $this->contextName, $element, $providerBehaviour, $resourceMeta);
            $this->eventDispatcher->dispatch(DynamicSearchEvents::NEW_DATA_AVAILABLE, $newDataEvent);
        }
    }

    /**
     * @param string $type
     *
     * @return array
     */
    protected function getTypeOptions(string $type)
    {
        return array_filter($this->indexOptions, function ($option) use ($type) {
            return strpos($option, sprintf('%s_', $type)) !== false;
        }, ARRAY_FILTER_USE_KEY);
    }
}