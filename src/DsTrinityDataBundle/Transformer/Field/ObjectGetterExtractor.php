<?php

namespace DsTrinityDataBundle\Transformer\Field;

use DynamicSearchBundle\Transformer\Container\DataContainerInterface;
use DynamicSearchBundle\Transformer\Container\FieldContainer;
use DynamicSearchBundle\Transformer\Container\FieldContainerInterface;
use DynamicSearchBundle\Transformer\FieldTransformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectGetterExtractor implements FieldTransformerInterface
{
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
    public function transformData(array $options, string $dispatchTransformerName, DataContainerInterface $transformedData): ?FieldContainerInterface
    {
        if (!$transformedData->hasDataAttribute('type')) {
            return null;
        }

        $data = $transformedData->getDataAttribute('data');
        $type = $transformedData->getDataAttribute('type');
        $dataType = $transformedData->getDataAttribute('data_type');

        if (!method_exists($data, $options['argument'])) {
            return null;
        }

        $value = call_user_func([$data, $options['argument']]);
        if (!is_string($value)) {
            return null;
        }

        return new FieldContainer($value);

    }
}