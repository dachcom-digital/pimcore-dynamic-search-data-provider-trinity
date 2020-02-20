<?php

namespace DsTrinityDataBundle\Provider;

use DsTrinityDataBundle\Service\DataProviderServiceInterface;
use DynamicSearchBundle\Context\ContextDataInterface;
use DynamicSearchBundle\EventDispatcher\DynamicSearchEventDispatcherInterface;
use DynamicSearchBundle\Normalizer\Resource\ResourceMetaInterface;
use DynamicSearchBundle\Provider\DataProviderInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrinityDataProvider implements DataProviderInterface
{
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
     * {@inheritdoc}
     */
    public function setOptions(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp(ContextDataInterface $contextData)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function coolDown(ContextDataInterface $contextData)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function cancelledShutdown(ContextDataInterface $contextData)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function emergencyShutdown(ContextDataInterface $contextData)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function checkUntrustedResourceProxy(ContextDataInterface $contextData, $resource)
    {
        $this->setupDataProvider($contextData);

        return $this->dataProvider->checkResourceProxy($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function validateUntrustedResource(ContextDataInterface $contextData, $resource)
    {
        $this->setupDataProvider($contextData);

        return $this->dataProvider->validate($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function provideAll(ContextDataInterface $contextData)
    {
        $this->setupDataProvider($contextData);

        $this->dataProvider->fetchListData();
    }

    /**
     * {@inheritdoc}
     */
    public function provideSingle(ContextDataInterface $contextData, ResourceMetaInterface $resourceMeta)
    {
        $this->dataProvider->setContextName($contextData->getName());
        $this->dataProvider->setContextDispatchType($contextData->getContextDispatchType());
        $this->dataProvider->setIndexOptions($this->configuration);

        $this->dataProvider->fetchSingleData($resourceMeta);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver, string $providerBehaviour)
    {
        $this->configureAlwaysOptions($resolver);

        if ($providerBehaviour === self::PROVIDER_BEHAVIOUR_FULL_DISPATCH) {
            $this->configureFullDispatchOptions($resolver);
        } elseif ($providerBehaviour === self::PROVIDER_BEHAVIOUR_SINGLE_DISPATCH) {
            $this->configureSingleDispatchOptions($resolver);
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureAlwaysOptions(OptionsResolver $resolver)
    {
        $defaults = [
            // assets
            'index_asset'                      => false,
            'asset_data_builder_identifier'    => 'default',
            'asset_additional_params'          => [],
            'asset_types'                      => array_filter(Asset::$types, function ($type) {
                return $type !== 'folder';
            }),
            // objects
            'index_object'                     => false,
            'object_ignore_unpublished'        => true,
            'object_data_builder_identifier'   => 'default',
            'object_class_names'               => [],
            'object_additional_params'         => [],
            'object_proxy_identifier'          => 'default',
            'object_proxy_settings'            => [/* defined in given proxy resolver */],
            'object_types'                     => array_filter(DataObject::$types, function ($type) {
                return $type !== 'folder';
            }),
            // documents
            'index_document'                   => false,
            'document_ignore_unpublished'      => true,
            'document_data_builder_identifier' => 'default',
            'document_additional_params'       => [],
            'document_types'                   => array_filter(Document::$types, function ($type) {
                return $type !== 'folder';
            }),
        ];

        $resolver->setDefaults($defaults);
        $resolver->setRequired(array_keys($defaults));
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureFullDispatchOptions(OptionsResolver $resolver)
    {
        $defaults = [
            'asset_limit'    => 0,
            'object_limit'   => 0,
            'document_limit' => 0,
        ];

        $resolver->setDefaults($defaults);
        $resolver->setRequired(array_keys($defaults));
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureSingleDispatchOptions(OptionsResolver $resolver)
    {
        $defaults = [];

        $resolver->setDefaults($defaults);
        $resolver->setRequired(array_keys($defaults));
    }

    /**
     * @param ContextDataInterface $contextData
     */
    protected function setupDataProvider(ContextDataInterface $contextData)
    {
        $this->dataProvider->setContextName($contextData->getName());
        $this->dataProvider->setContextDispatchType($contextData->getContextDispatchType());
        $this->dataProvider->setIndexOptions($this->configuration);
    }

}
