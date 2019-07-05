<?php

namespace DsTrinityDataBundle\Transformer\Field;

use DynamicSearchBundle\Transformer\Container\DocumentContainerInterface;
use DynamicSearchBundle\Transformer\Container\FieldContainer;
use DynamicSearchBundle\Transformer\Container\FieldContainerInterface;
use DynamicSearchBundle\Transformer\FieldTransformerInterface;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElementIdExtractor implements FieldTransformerInterface
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
        return false;
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

        $data = $transformedData->getAttribute('data');
        $type = $transformedData->getAttribute('type');
        $dataType = $transformedData->getAttribute('data_type');

        if (!$data instanceof ElementInterface) {
            return null;
        }

        $value = sprintf('%s_%d', $type, $data->getId());

        return new FieldContainer($value);

    }
}