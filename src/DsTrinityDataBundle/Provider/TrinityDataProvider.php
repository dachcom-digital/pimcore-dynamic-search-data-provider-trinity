<?php

namespace DsTrinityDataBundle\Provider;

use DsTrinityDataBundle\DsTrinityDataBundle;
use DsTrinityDataBundle\Service\DataProviderServiceInterface;
use DynamicSearchBundle\Context\ContextDataInterface;
use DynamicSearchBundle\EventDispatcher\DynamicSearchEventDispatcherInterface;
use DynamicSearchBundle\Exception\ProviderException;
use DynamicSearchBundle\Logger\LoggerInterface;
use DynamicSearchBundle\Provider\DataProviderInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrinityDataProvider implements DataProviderInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var DynamicSearchEventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var DataProviderServiceInterface
     */
    protected $dataProvider;

    /**
     * @param DynamicSearchEventDispatcherInterface $eventDispatcher
     * @param DataProviderServiceInterface          $dataProvider
     */
    public function __construct(
        DynamicSearchEventDispatcherInterface $eventDispatcher,
        DataProviderServiceInterface $dataProvider
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->dataProvider = $dataProvider;
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
    public function setOptions(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritDoc}
     */
    public function warmUp(ContextDataInterface $contextData)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function coolDown(ContextDataInterface $contextData)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function cancelledShutdown(ContextDataInterface $contextData)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function emergencyShutdown(ContextDataInterface $contextData)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(ContextDataInterface $contextData)
    {
        $runtimeValues = $this->validateRuntimeValues($contextData->getContextDispatchType(), $contextData->getRuntimeValues());

        $this->dataProvider->setLogger($this->logger);
        $this->dataProvider->setContextName($contextData->getName());
        $this->dataProvider->setContextDispatchType($contextData->getContextDispatchType());
        $this->dataProvider->setIndexOptions($this->configuration);
        $this->dataProvider->setRuntimeValues($runtimeValues);

        if ($contextData->getContextDispatchType() === ContextDataInterface::CONTEXT_DISPATCH_TYPE_INDEX) {
            $this->dataProvider->fetchIndexData();
        } elseif ($contextData->getContextDispatchType() === ContextDataInterface::CONTEXT_DISPATCH_TYPE_INSERT) {
            $this->dataProvider->fetchInsertData();
        } elseif ($contextData->getContextDispatchType() === ContextDataInterface::CONTEXT_DISPATCH_TYPE_UPDATE) {
            $this->dataProvider->fetchUpdateData();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $defaults = [
            'index_asset'                   => false,
            'asset_data_builder_identifier' => 'default',
            'asset_types'                   => Asset::$types,
            'asset_limit'                   => 0,
            'asset_additional_params'       => [],

            'index_object'                   => false,
            'object_data_builder_identifier' => 'default',
            'object_types'                   => DataObject::$types,
            'object_class_names'             => [],
            'object_ignore_unpublished'      => true,
            'object_limit'                   => 0,
            'object_additional_params'       => [],

            'index_document'                   => false,
            'document_data_builder_identifier' => 'default',
            'document_types'                   => Document::$types,
            'document_ignore_unpublished'      => true,
            'document_limit'                   => 0,
            'document_additional_params'       => [],
        ];

        $resolver->setDefaults($defaults);
        $resolver->setRequired(array_keys($defaults));
    }

    /**
     * @param string $contextDispatchType
     * @param array  $runtimeValues
     *
     * @return array
     * @throws ProviderException
     */
    protected function validateRuntimeValues(string $contextDispatchType, array $runtimeValues = [])
    {
        $errorMessage = null;

        switch ($contextDispatchType) {
            case ContextDataInterface::CONTEXT_DISPATCH_TYPE_UPDATE:
                if (!isset($runtimeValues['id']) || empty($runtimeValues['id'])) {
                    $errorMessage = 'no "id" runtime option given. value cannot be empty';
                }
                break;
            case ContextDataInterface::CONTEXT_DISPATCH_TYPE_INSERT:
                if (!isset($runtimeValues['path']) || !is_string($runtimeValues['path'])) {
                    $errorMessage = 'no "path" runtime option given. needs to be a valid string';
                }
                break;
            case ContextDataInterface::CONTEXT_DISPATCH_TYPE_DELETE:
                if (!isset($runtimeValues['id']) || empty($runtimeValues['id'])) {
                    $errorMessage = 'no "id" runtime option given. value cannot be empty';
                }
                break;
        }

        if ($errorMessage !== null) {
            throw new ProviderException(sprintf('Runtime Options validation failed. Error was: %s', $errorMessage), DsTrinityDataBundle::PROVIDER_NAME);
        }

        return $runtimeValues;
    }

}