<?php

namespace DsTrinityDataBundle\Transformer\Field;

use DynamicSearchBundle\Transformer\Container\DocumentContainerInterface;
use DynamicSearchBundle\Transformer\Container\FieldContainer;
use DynamicSearchBundle\Transformer\Container\FieldContainerInterface;
use DynamicSearchBundle\Transformer\FieldTransformerInterface;
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
    public function transformData(string $dispatchTransformerName, DocumentContainerInterface $transformedData): ?FieldContainerInterface
    {
        if (!$transformedData->hasAttribute('type')) {
            return null;
        }

        $data = $transformedData->getResource();
        $type = $transformedData->getAttribute('type');
        $dataType = $transformedData->getAttribute('data_type');

        if (!method_exists($data, $this->options['method'])) {
            return null;
        }

        $value = call_user_func([$data, $this->options['method']], $this->options['locale']);
        if (!is_string($value)) {
            return null;
        }

        return new FieldContainer($value);

    }
}