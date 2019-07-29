<?php

namespace DsTrinityDataBundle\Resource\FieldTransformer\Document;

use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use DynamicSearchBundle\Resource\FieldTransformerInterface;
use Pimcore\Model\Document\Page;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentMetaExtractor implements FieldTransformerInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['type']);
        $resolver->setAllowedTypes('type', ['string']);
        $resolver->setAllowedValues('type', ['description', 'title']);
        $resolver->setDefaults([
            'type' => 'title'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function transformData(string $dispatchTransformerName, ResourceContainerInterface $resourceContainer)
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
        } elseif ($this->options['type'] === 'title') {
            return $data->getTitle();
        }

        return null;
    }
}
