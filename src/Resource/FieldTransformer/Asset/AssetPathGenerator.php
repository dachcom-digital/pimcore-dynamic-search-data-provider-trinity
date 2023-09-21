<?php

namespace DsTrinityDataBundle\Resource\FieldTransformer\Asset;

use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use DynamicSearchBundle\Resource\FieldTransformerInterface;
use Pimcore\Model\Asset\Document;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssetPathGenerator implements FieldTransformerInterface
{
    protected array $options;

    public function configureOptions(OptionsResolver $resolver): void
    {
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function transformData(string $dispatchTransformerName, ResourceContainerInterface $resourceContainer): mixed
    {
        $document = $resourceContainer->getResource();
        if (!$document instanceof Document) {
            return null;
        }

        return $document->getRealFullPath();
    }
}
