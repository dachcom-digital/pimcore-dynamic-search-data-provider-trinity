<?php

namespace DsTrinityDataBundle\Service;

use DsTrinityDataBundle\DsTrinityDataBundle;
use DsTrinityDataBundle\Registry\DataBuilderRegistryInterface;
use DsTrinityDataBundle\Service\Builder\DataBuilderInterface;
use DynamicSearchBundle\DynamicSearchEvents;
use DynamicSearchBundle\Event\NewDataEvent;
use DynamicSearchBundle\Logger\LoggerInterface;
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
     * @var array
     */
    protected $runtimeValues;

    /**
     * @param EventDispatcherInterface     $eventDispatcher
     * @param DataBuilderRegistryInterface $dataBuilderRegistry
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        DataBuilderRegistryInterface $dataBuilderRegistry
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->dataBuilderRegistry = $dataBuilderRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
    public function setRuntimeValues(array $runtimeValues)
    {
        $this->runtimeValues = $runtimeValues;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchIndexData()
    {
        foreach (['asset', 'document', 'object'] as $type) {
            $this->fetchByType($type);
        }

    }

    /**
     * {@inheritDoc}
     */
    public function fetchInsertData()
    {
        $id = $this->runtimeValues['id'];
        $indexId = explode('_', $id);

        $elementType = $indexId[0];
        $elementId = (int) $indexId[1];

        if (!in_array($elementType, ['asset', 'document', 'object'])) {
            $this->log('error', sprintf('cannot insert data from identifier "%s". wrong type "%s" given', $id, $elementType));
            return;
        }

        $this->fetchByType($elementType, $elementId);

    }

    /**
     * {@inheritDoc}
     */
    public function fetchUpdateData()
    {
        $id = $this->runtimeValues['id'];
        $indexId = explode('_', $id);

        $elementType = $indexId[0];
        $elementId = (int) $indexId[1];

        if (!in_array($elementType, ['asset', 'document', 'object'])) {
            $this->log('error', sprintf('cannot insert data from identifier "%s". wrong type "%s" given', $id, $elementType));
            return;
        }

        $this->fetchByType($elementType, $elementId);
    }

    /**
     * @param string $type
     * @param null   $id
     */
    protected function fetchByType(string $type, $id = null)
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
        $options['id'] = $id;

        $elements = $builder->build($options);

        $this->dispatchData($elements);
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
     * @param $elements
     */
    protected function dispatchData(array $elements)
    {
        foreach ($elements as $element) {
            $newDataEvent = new NewDataEvent($this->contextDispatchType, $this->contextName, $element, $this->runtimeValues);
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