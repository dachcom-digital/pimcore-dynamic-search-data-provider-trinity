<?php

namespace DsTrinityDataBundle\Resource\FieldTransformer\Common;

use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use DynamicSearchBundle\Resource\FieldTransformerInterface;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Property;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElementPropertyExtractor implements FieldTransformerInterface
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
        $resolver->setRequired(['property', 'object_getter', 'allow_inherited_value']);
        $resolver->setAllowedTypes('property', ['string']);
        $resolver->setAllowedTypes('object_getter', ['string', 'null']);
        $resolver->setAllowedTypes('allow_inherited_value', ['bool']);
        $resolver->setDefaults([
            'property'              => null,
            'object_getter'         => null,
            'allow_inherited_value' => true
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

        $element = $resourceContainer->getResource();
        if (!$element instanceof ElementInterface) {
            return null;
        }

        $properties = $element->getProperties();
        if (!isset($properties[$this->options['property']])) {
            return null;
        }

        $property = $properties[$this->options['property']];
        if (!$property instanceof Property) {
            return null;
        }

        if ($property->isInherited() === true && $this->options['allow_inherited_value'] === false) {
            return null;
        }

        $value = $property->getData();

        if (is_object($value)) {
            if ($this->options['object_getter'] === null) {
                return null;
            }

            if (method_exists($value, $this->options['object_getter'])) {
                return call_user_func([$value, $this->options['object_getter']]);
            }

            return null;
        }

        return $value;
    }
}
