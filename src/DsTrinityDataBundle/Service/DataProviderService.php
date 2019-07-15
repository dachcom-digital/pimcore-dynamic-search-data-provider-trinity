<?php

namespace DsTrinityDataBundle\Service;

use DsTrinityDataBundle\DsTrinityDataBundle;
use DsTrinityDataBundle\Registry\DataBuilderRegistryInterface;
use DsTrinityDataBundle\Service\Builder\DataBuilderInterface;
use DsWebCrawlerBundle\DsWebCrawlerBundle;
use DynamicSearchBundle\DynamicSearchEvents;
use DynamicSearchBundle\Event\ErrorEvent;
use DynamicSearchBundle\Event\NewDataEvent;
use DynamicSearchBundle\Logger\LoggerInterface;
use DynamicSearchBundle\Normalizer\Resource\ResourceMetaInterface;
use DynamicSearchBundle\Provider\DataProviderInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Model\Element\ElementInterface;
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
     * {@inheritdoc}
     */
    public function setContextName(string $contextName)
    {
        $this->contextName = $contextName;
    }

    /**
     * {@inheritdoc}
     */
    public function setContextDispatchType(string $dispatchType)
    {
        $this->contextDispatchType = $dispatchType;
    }

    /**
     * {@inheritdoc}
     */
    public function setIndexOptions(array $indexOptions)
    {
        $this->indexOptions = $indexOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($resource)
    {
        if (!$resource instanceof ElementInterface) {
            return false;
        }

        $builder = null;
        $type = null;

        if ($resource instanceof Document) {
            $type = 'document';
        } elseif ($resource instanceof Asset) {
            $type = 'asset';
        } elseif ($resource instanceof DataObject) {
            $type = 'object';
        }

        if ($type === null) {
            return false;
        }

        if ($this->indexOptions[sprintf('index_%s', $type)] === false) {
            return false;
        }

        $builderIdentifier = sprintf('%s_data_builder_identifier', $type);
        $builder = $this->dataBuilderRegistry->getByTypeAndIdentifier($type, $this->indexOptions[$builderIdentifier]);

        if (!$builder instanceof DataBuilderInterface) {
            return false;
        }

        $options = $this->getTypeOptions($type);
        $element = $builder->buildByIdList((int) $resource->getId(), $options);

        return $element instanceof ElementInterface;

    }

    /**
     * {@inheritdoc}
     */
    public function fetchListData()
    {
        $this->addSignalListener();

        foreach (['asset', 'document', 'object'] as $type) {
            $this->fetchByType($type, DataProviderInterface::PROVIDER_BEHAVIOUR_FULL_DISPATCH);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetchSingleData(ResourceMetaInterface $resourceMeta)
    {
        $elementType = $resourceMeta->getResourceCollectionType();
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

            $this->dispatchProcessControlSignal();
        }
    }

    protected function addSignalListener()
    {
        if (php_sapi_name() !== 'cli') {
            return;
        }

        if (!function_exists('pcntl_signal')) {
            return;
        }

        declare(ticks=1);

        pcntl_signal(SIGTERM, [$this, 'handleSignal']);
        pcntl_signal(SIGINT, [$this, 'handleSignal']);
        pcntl_signal(SIGHUP, [$this, 'handleSignal']);
        pcntl_signal(SIGQUIT, [$this, 'handleSignal']);
    }

    protected function dispatchProcessControlSignal()
    {
        if (!function_exists('pcntl_signal')) {
            return;
        }

        pcntl_signal_dispatch();
    }

    public function handleSignal($signal)
    {
        $newDataEvent = new ErrorEvent($this->contextName, sprintf('crawler has been stopped by user (signal: %s)', $signal), DsWebCrawlerBundle::PROVIDER_NAME);
        $this->eventDispatcher->dispatch(DynamicSearchEvents::ERROR_DISPATCH_ABORT, $newDataEvent);
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
