<?php

namespace DsTrinityDataBundle\Resource\FieldTransformer\Document;

use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use DynamicSearchBundle\Resource\FieldTransformerInterface;
use Pimcore\Model\Document\Page;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentMetaExtractor implements FieldTransformerInterface
{
    protected array $options;

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['type']);
        $resolver->setAllowedTypes('type', ['string']);
        $resolver->setAllowedValues('type', ['description', 'title']);
        $resolver->setDefaults([
            'type' => 'title'
        ]);
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

        $data = $resourceContainer->getResource();
        if (!$data instanceof Page) {
            return null;
        }

        if ($this->options['type'] === 'description') {
            return $data->getDescription();
        }

        if ($this->options['type'] === 'title') {
            return $data->getTitle();
        }

        return null;
    }
}
