<?php

namespace DsTrinityDataBundle\Guard;

use DsTrinityDataBundle\DsTrinityDataBundle;
use DynamicSearchBundle\Guard\ContextGuardInterface;
use DynamicSearchBundle\Normalizer\Resource\ResourceMetaInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Model\Element\ElementInterface;

class DefaultDataResourceGuard implements ContextGuardInterface
{
    /**
     * {@inheritdoc}
     */
    public function isValidateDataResource(string $contextName, string $dataProviderName, array $dataProviderOptions, ResourceMetaInterface $resourceMeta, $resource)
    {
        if ($dataProviderName !== DsTrinityDataBundle::PROVIDER_NAME) {
            return true;
        }

        if (!$resource instanceof ElementInterface) {
            return false;
        }

        if ($resource instanceof Document) {
            if ($dataProviderOptions['index_document'] === false) {
                return false;
            }

            if (!in_array($resource->getType(), $dataProviderOptions['document_types'])) {
                return false;
            }
        }

        if ($resource instanceof Asset) {
            if ($dataProviderOptions['index_asset'] === false) {
                return false;
            }

            if (!in_array($resource->getType(), $dataProviderOptions['asset_types'])) {
                return false;
            }
        }

        if ($resource instanceof DataObject) {
            if ($dataProviderOptions['index_object'] === false) {
                return false;
            }

            if (!in_array($resource->getType(), $dataProviderOptions['object_types'])) {
                return false;
            }
        }

        return true;
    }
}
