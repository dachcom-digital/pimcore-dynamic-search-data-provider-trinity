<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace DsTrinityDataBundle\Resource\FieldTransformer\Common;

use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use DynamicSearchBundle\Resource\FieldTransformerInterface;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElementPropertyExtractor implements FieldTransformerInterface
{
    protected array $options;

    public function configureOptions(OptionsResolver $resolver): void
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

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function transformData(string $dispatchTransformerName, ResourceContainerInterface $resourceContainer): mixed
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
