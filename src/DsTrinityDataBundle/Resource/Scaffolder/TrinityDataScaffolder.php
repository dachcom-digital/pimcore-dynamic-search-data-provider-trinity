<?php

namespace DsTrinityDataBundle\Resource\Scaffolder;

use DynamicSearchBundle\Context\ContextDefinitionInterface;
use DynamicSearchBundle\Resource\ResourceScaffolderInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;

class TrinityDataScaffolder implements ResourceScaffolderInterface
{
    /**
     * @var ContextDefinitionInterface
     */
    protected $contextDefinition;

    /**
     * {@inheritdoc}
     */
    public function isBaseResource($resource): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable($resource): bool
    {
        if ($resource instanceof Asset) {
            return true;
        }

        if ($resource instanceof Document) {
            return true;
        }

        if ($resource instanceof DataObject\Concrete) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setup(ContextDefinitionInterface $contextDefinition, $resource): array
    {
        $this->contextDefinition = $contextDefinition;

        $type = null;
        $dataType = null;

        if ($resource instanceof Asset) {
            $type = 'asset';
            $dataType = $resource->getType();
        } elseif ($resource instanceof Document) {
            $type = 'document';
            $dataType = $resource->getType();
        } elseif ($resource instanceof DataObject\Concrete) {
            $type = 'object';
            $dataType = $resource->getType();
        }

        return [
            'type'      => $type,
            'data_type' => $dataType
        ];
    }
}
