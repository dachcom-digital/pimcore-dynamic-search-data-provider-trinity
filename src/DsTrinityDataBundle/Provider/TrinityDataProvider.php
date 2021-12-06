<?php

namespace DsTrinityDataBundle\Provider;

use DsTrinityDataBundle\Service\DataProviderServiceInterface;
use DynamicSearchBundle\Context\ContextDefinitionInterface;
use DynamicSearchBundle\Normalizer\Resource\ResourceMetaInterface;
use DynamicSearchBundle\Provider\DataProviderInterface;
use DynamicSearchBundle\Provider\DataProviderValidationAwareInterface;
use DynamicSearchBundle\Resource\ResourceCandidateInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrinityDataProvider implements DataProviderInterface, DataProviderValidationAwareInterface
{
    protected DataProviderServiceInterface $dataProvider;
    protected array $options;

    public function __construct(DataProviderServiceInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $options = [
            'always'                                 => function (OptionsResolver $spoolResolver) {

                $options = [
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
                    })
                ];

                $spoolResolver->setDefaults($options);
                $spoolResolver->setRequired(array_keys($options));
            },
            self::PROVIDER_BEHAVIOUR_FULL_DISPATCH   => function (OptionsResolver $spoolResolver) {

                $options = [
                    'asset_limit'    => 0,
                    'object_limit'   => 0,
                    'document_limit' => 0,
                ];

                $spoolResolver->setDefaults($options);
                $spoolResolver->setRequired(array_keys($options));
            },
            self::PROVIDER_BEHAVIOUR_SINGLE_DISPATCH => function (OptionsResolver $spoolResolver) {
                $spoolResolver->setDefaults([]);
            }
        ];

        $resolver->setDefaults($options);
        $resolver->setRequired(array_keys($options));
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function warmUp(ContextDefinitionInterface $contextDefinition): void
    {
    }

    public function coolDown(ContextDefinitionInterface $contextDefinition): void
    {
    }

    public function cancelledShutdown(ContextDefinitionInterface $contextDefinition): void
    {
    }

    public function emergencyShutdown(ContextDefinitionInterface $contextDefinition): void
    {
    }

    /**
     * @deprecated
     */
    public function checkUntrustedResourceProxy(ContextDefinitionInterface $contextDefinition, $resource)
    {
        // we're only able to validate elements here
        if (!$resource instanceof ElementInterface) {
            return null;
        }

        $this->setupDataProvider($contextDefinition);

        return $this->dataProvider->checkResourceProxy($resource);
    }

    /**
     * @deprecated
     */
    public function validateUntrustedResource(ContextDefinitionInterface $contextDefinition, $resource)
    {
        // we're only able to validate elements here
        if (!$resource instanceof ElementInterface) {
            return false;
        }

        $this->setupDataProvider($contextDefinition);

        return $this->dataProvider->validate($resource);
    }

    public function validateResource(ContextDefinitionInterface $contextDefinition, ResourceCandidateInterface $resourceCandidate): void
    {
        // we're only able to validate elements here
        $resource = $resourceCandidate->getResource();

        if (!$resource instanceof ElementInterface) {
            $resourceCandidate->setResource(null);

            return;
        }

        $this->setupDataProvider($contextDefinition);

        $isValidResource = $this->dataProvider->validate($resource);

        if ($isValidResource === false) {
            $resourceCandidate->setResource(null);
        }
    }

    public function provideAll(ContextDefinitionInterface $contextDefinition): void
    {
        $this->setupDataProvider($contextDefinition);

        $this->dataProvider->fetchListData();
    }

    public function provideSingle(ContextDefinitionInterface $contextDefinition, ResourceMetaInterface $resourceMeta): void
    {
        $this->dataProvider->setContextName($contextDefinition->getName());
        $this->dataProvider->setContextDispatchType($contextDefinition->getContextDispatchType());
        $this->dataProvider->setIndexOptions($this->options);

        $this->dataProvider->fetchSingleData($resourceMeta);
    }

    protected function setupDataProvider(ContextDefinitionInterface $contextDefinition): void
    {
        $this->dataProvider->setContextName($contextDefinition->getName());
        $this->dataProvider->setContextDispatchType($contextDefinition->getContextDispatchType());
        $this->dataProvider->setIndexOptions($this->options);
    }

}
