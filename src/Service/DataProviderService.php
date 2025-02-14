<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace DsTrinityDataBundle\Service;

use DsTrinityDataBundle\DsTrinityDataBundle;
use DsTrinityDataBundle\Registry\DataBuilderRegistryInterface;
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
    protected string $contextName;
    protected string $contextDispatchType;
    protected array $indexOptions;

    public function __construct(
        protected LoggerInterface $logger,
        protected EventDispatcherInterface $eventDispatcher,
        protected DataBuilderRegistryInterface $dataBuilderRegistry
    ) {
    }

    public function setContextName(string $contextName): void
    {
        $this->contextName = $contextName;
    }

    public function setContextDispatchType(string $dispatchType): void
    {
        $this->contextDispatchType = $dispatchType;
    }

    public function setIndexOptions(array $indexOptions): void
    {
        $this->indexOptions = $indexOptions;
    }

    public function validate(ElementInterface $resource): bool
    {
        $type = $this->getResourceType($resource);

        if ($type === null) {
            return false;
        }

        if ($this->indexOptions[sprintf('index_%s', $type)] === false) {
            return false;
        }

        $builderIdentifier = sprintf('%s_data_builder_identifier', $type);
        $builder = $this->dataBuilderRegistry->getByTypeAndIdentifier($type, $this->indexOptions[$builderIdentifier]);

        $options = $this->getTypeOptions($type);
        $element = $builder->buildByIdList((int) $resource->getId(), $options);

        return $element instanceof ElementInterface;
    }

    public function fetchListData(): void
    {
        $this->addSignalListener();

        foreach (['asset', 'document', 'object'] as $type) {
            $this->fetchByType($type, DataProviderInterface::PROVIDER_BEHAVIOUR_FULL_DISPATCH);
        }
    }

    public function fetchSingleData(ResourceMetaInterface $resourceMeta): void
    {
        $elementType = $resourceMeta->getResourceCollectionType();
        $elementId = $resourceMeta->getResourceId();

        if (!in_array($elementType, ['asset', 'document', 'object'])) {
            $this->log('error', sprintf('cannot insert data from identifier "%s". wrong type "%s" given', $elementId, $elementType));

            return;
        }

        $this->fetchByTypeAndId($elementType, DataProviderInterface::PROVIDER_BEHAVIOUR_SINGLE_DISPATCH, $elementId, $resourceMeta);
    }

    protected function fetchByType(string $type, string $providerBehaviour): void
    {
        if ($this->indexOptions[sprintf('index_%s', $type)] === false) {
            return;
        }

        $builderIdentifier = sprintf('%s_data_builder_identifier', $type);

        $builder = $this->dataBuilderRegistry->getByTypeAndIdentifier($type, $this->indexOptions[$builderIdentifier]);

        $options = $this->getTypeOptions($type);
        $elements = $builder->buildByList($options);

        $this->dispatchData($elements, $providerBehaviour);
    }

    protected function fetchByTypeAndId(string $type, string $providerBehaviour, int $id, ?ResourceMetaInterface $resourceMeta): void
    {
        if ($this->indexOptions[sprintf('index_%s', $type)] === false) {
            return;
        }

        $builderIdentifier = sprintf('%s_data_builder_identifier', $type);
        $builder = $this->dataBuilderRegistry->getByTypeAndIdentifier($type, $this->indexOptions[$builderIdentifier]);

        $element = $builder->buildById($id);

        if ($element instanceof ElementInterface) {
            $this->dispatchElement($element, $providerBehaviour, $resourceMeta);
        }
    }

    protected function log(string $level, string $message): void
    {
        $this->logger->log($level, $message, DsTrinityDataBundle::PROVIDER_NAME, $this->contextName);
    }

    protected function dispatchData(\Generator $elements, string $providerBehaviour, ?ResourceMetaInterface $resourceMeta = null): void
    {
        foreach ($elements as $element) {
            $this->dispatchElement($element, $providerBehaviour, $resourceMeta);
        }
    }

    protected function dispatchElement(ElementInterface $element, string $providerBehaviour, ?ResourceMetaInterface $resourceMeta = null): void
    {
        $newDataEvent = new NewDataEvent($this->contextDispatchType, $this->contextName, $element, $providerBehaviour, $resourceMeta);
        $this->eventDispatcher->dispatch($newDataEvent, DynamicSearchEvents::NEW_DATA_AVAILABLE);

        $this->dispatchProcessControlSignal();
    }

    protected function addSignalListener(): void
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

    protected function dispatchProcessControlSignal(): void
    {
        if (!function_exists('pcntl_signal')) {
            return;
        }

        pcntl_signal_dispatch();
    }

    public function handleSignal($signal): void
    {
        $newDataEvent = new ErrorEvent($this->contextName, sprintf('crawler has been stopped by user (signal: %s)', $signal), DsTrinityDataBundle::PROVIDER_NAME);
        $this->eventDispatcher->dispatch($newDataEvent, DynamicSearchEvents::ERROR_DISPATCH_ABORT);
    }

    protected function getResourceType(ElementInterface $resource): ?string
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

    protected function getTypeOptions(string $type): array
    {
        return array_filter($this->indexOptions, static function ($option) use ($type) {
            return str_contains($option, sprintf('%s_', $type));
        }, ARRAY_FILTER_USE_KEY);
    }
}
