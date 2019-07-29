<?php

namespace DsTrinityDataBundle\Resource\FieldTransformer\Object;

use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use DynamicSearchBundle\Resource\FieldTransformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectGetterExtractor implements FieldTransformerInterface
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
        $resolver->setRequired(['method', 'arguments']);
        $resolver->setAllowedTypes('method', ['string']);
        $resolver->setAllowedTypes('arguments', ['array']);
        $resolver->setDefaults([
            'method'    => 'id',
            'arguments' => []
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
        if (!method_exists($data, $this->options['method'])) {
            return null;
        }

        $value = call_user_func_array([$data, $this->options['method']], $this->options['arguments']);
        if (!is_string($value)) {
            return null;
        }

        return $value;
    }
}
