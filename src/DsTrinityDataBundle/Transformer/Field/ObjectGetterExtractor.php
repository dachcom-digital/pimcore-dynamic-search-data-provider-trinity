<?php

namespace DsTrinityDataBundle\Transformer\Field;

use DynamicSearchBundle\Transformer\Container\DocumentContainerInterface;
use DynamicSearchBundle\Transformer\Container\FieldContainer;
use DynamicSearchBundle\Transformer\Container\FieldContainerInterface;
use DynamicSearchBundle\Transformer\FieldTransformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectGetterExtractor implements FieldTransformerInterface
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
        $resolver->setRequired(['argument']);
        $resolver->setAllowedTypes('argument', ['string']);
        $resolver->setDefaults([
            'argument' => 'id'
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
    public function transformData(string $dispatchTransformerName, DocumentContainerInterface $transformedData): ?FieldContainerInterface
    {
        if (!$transformedData->hasAttribute('type')) {
            return null;
        }

        $data = $transformedData->getResource();
        $type = $transformedData->getAttribute('type');
        $dataType = $transformedData->getAttribute('data_type');

        if (!method_exists($data, $this->options['argument'])) {
            return null;
        }

        $value = call_user_func([$data, $this->options['argument']]);
        if (!is_string($value)) {
            return null;
        }

        return new FieldContainer($value);

    }
}