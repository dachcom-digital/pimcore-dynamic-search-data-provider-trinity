<?php

namespace DsTrinityDataBundle\Service;

use DsTrinityDataBundle\DsTrinityDataBundle;
use DsTrinityDataBundle\Registry\DataBuilderRegistryInterface;
use DsTrinityDataBundle\Registry\ProxyResolverRegistryInterface;
use DsTrinityDataBundle\Resource\ProxyResolver\ProxyResolverInterface;
use DsTrinityDataBundle\Service\Builder\DataBuilderInterface;
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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
     * @var ProxyResolverRegistryInterface
     */
    protected $proxyResolverRegistry;

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
     * @param LoggerInterface                $logger
     * @param EventDispatcherInterface       $eventDispatcher
     * @param DataBuilderRegistryInterface   $dataBuilderRegistry
     * @param ProxyResolverRegistryInterface $proxyResolverRegistry
     */
    public function __construct(
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        DataBuilderRegistryInterface $dataBuilderRegistry,
        ProxyResolverRegistryInterface $proxyResolverRegistry
    ) {
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->dataBuilderRegistry = $dataBuilderRegistry;
        $this->proxyResolverRegistry = $proxyResolverRegistry;
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
    public function checkResourceProxy(ElementInterface $resource)
    {
        $proxyResolver = null;

        $type = $this->getResourceType($resource);

        if ($type === null) {
            return null;
        }

        if ($this->indexOptions[sprintf('index_%s', $type)] === false) {
            return null;
        }

        $options = $this->getTypeOptions($type);
        $proxyIdentifier = sprintf('%s_proxy_identifier', $type);
        $proxyOptionsIdentifier = sprintf('%s_proxy_settings', $type);

        if (!isset($options[$proxyIdentifier])) {
            return null;
        }

        if (!isset($options[$proxyOptionsIdentifier])) {
            return null;
        }

        $proxyResolver = $this->proxyResolverRegistry->getByTypeAndIdentifier($type, $this->indexOptions[$proxyIdentifier]);

        if (!$proxyResolver instanceof ProxyResolverInterface) {
            return null;
        }

        $optionsResolver = new OptionsResolver();
        $proxyResolver->configureOptions($optionsResolver);

        $proxyOptions = $optionsResolver->resolve($options[$proxyOptionsIdentifier]);

        return $proxyResolver->resolveProxy($resource, $proxyOptions, ['contextDispatchType' => $this->contextDispatchType, 'contextName' => $this->contextName]);
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ElementInterface $resource)
    {
        if (!$resource instanceof ElementInterface) {
            return false;
        }

        $builder = null;
        $type = $this->getResourceType($resource);

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
     * @param string                     $type
     * @param string                     $providerBehaviour
     * @param int                        $id
     * @param ResourceMetaInterface|null $resourceMeta
     */
    protected function fetchByTypeAndId(string $type, string $providerBehaviour, $id, ?ResourceMetaInterface $resourceMeta)
    {
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
            $this->eventDispatcher->dispatch($newDataEvent, DynamicSearchEvents::NEW_DATA_AVAILABLE);

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
        $newDataEvent = new ErrorEvent($this->contextName, sprintf('crawler has been stopped by user (signal: %s)', $signal), DsTrinityDataBundle::PROVIDER_NAME);
        $this->eventDispatcher->dispatch($newDataEvent, DynamicSearchEvents::ERROR_DISPATCH_ABORT);
    }

    /**
     * @param ElementInterface $resource
     *
     * @return string|null
     */
    protected function getResourceType(ElementInterface $resource)
    {
        $type = null;

        if ($resource instanceof Document) {
            $type = 'document';
        } elseif ($resource instanceof Asset) {
            $type = 'asset';
        } elseif ($resource instanceof DataObject) {
            $type = 'object';
        }

        return $type;
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
