<?php

namespace DsTrinityDataBundle\Resource\FieldTransformer\Common;

use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use DynamicSearchBundle\Resource\FieldTransformerInterface;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElementKeyExtractor implements FieldTransformerInterface
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
        if (!$resourceContainer->hasAttribute('type')) {
            return null;
        }

        $element = $resourceContainer->getResource();
        if (!$element instanceof ElementInterface) {
            return null;
        }

        return $element->getKey();
    }
}
