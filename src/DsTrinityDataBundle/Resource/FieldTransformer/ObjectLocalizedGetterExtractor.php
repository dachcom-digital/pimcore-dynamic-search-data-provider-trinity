<?php

namespace DsTrinityDataBundle\Resource\FieldTransformer;

use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use DynamicSearchBundle\Resource\FieldTransformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectLocalizedGetterExtractor implements FieldTransformerInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['method', 'locale']);
        $resolver->setAllowedTypes('method', ['string']);
        $resolver->setAllowedTypes('locale', ['string']);
        $resolver->setDefaults([
            'locale' => 'en'
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function transformData(string $dispatchTransformerName, ResourceContainerInterface $resourceContainer)
    {
        if (!$resourceContainer->hasAttribute('type')) {
            return null;
        }

        $data = $resourceContainer->getResource();
        $type = $resourceContainer->getAttribute('type');
        $dataType = $resourceContainer->getAttribute('data_type');

        if (!method_exists($data, $this->options['method'])) {
            return null;
        }

        $value = call_user_func([$data, $this->options['method']], $this->options['locale']);
        if (!is_string($value)) {
            return null;
        }

        return $value;

    }
}