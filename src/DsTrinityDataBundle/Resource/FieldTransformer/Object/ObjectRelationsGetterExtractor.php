<?php

namespace DsTrinityDataBundle\Resource\FieldTransformer\Object;

use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use DynamicSearchBundle\Resource\FieldTransformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectRelationsGetterExtractor implements FieldTransformerInterface
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
        $resolver->setRequired(['relations', 'arguments', 'method']);
        $resolver->setAllowedTypes('relations', ['string']);
        $resolver->setAllowedTypes('arguments', ['array']);
        $resolver->setAllowedTypes('method', ['string']);
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
        $data = $resourceContainer->getResource();

        $relationsGetter = sprintf('get%s', ucfirst($this->options['relations']));

        if (!method_exists($data, $relationsGetter)) {
            return null;
        }

        $relations = call_user_func([$data, $relationsGetter]);
        if (!is_array($relations)) {
            return null;
        }

        $values = [];
        foreach ($relations as $relation) {
            if (!method_exists($relation, $this->options['method'])) {
                return null;
            }

            $values[] = call_user_func([$relation, $this->options['method']], $this->options['arguments']);
        }

        return $values;
    }
}
