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

namespace DsTrinityDataBundle\Resource\FieldTransformer\Object;

use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use DynamicSearchBundle\Resource\FieldTransformerInterface;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\ClassDefinition\LinkGeneratorInterface;
use Pimcore\Model\DataObject\Concrete;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectPathGenerator implements FieldTransformerInterface
{
    protected array $options;

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'arguments',
            'fetch_object_for_variant',
        ]);

        $resolver->setAllowedTypes('arguments', ['array']);
        $resolver->setAllowedTypes('fetch_object_for_variant', ['bool']);

        $resolver->setDefaults([
            'arguments'                => [],
            'fetch_object_for_variant' => false,
        ]);
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function transformData(string $dispatchTransformerName, ResourceContainerInterface $resourceContainer): mixed
    {
        $object = $resourceContainer->getResource();
        if (!$object instanceof DataObject) {
            return null;
        }

        if ($this->options['fetch_object_for_variant'] === true && $object->getType() === AbstractObject::OBJECT_TYPE_VARIANT) {
            while ($object->getType() === AbstractObject::OBJECT_TYPE_VARIANT) {
                $object = $object->getParent();
            }
        }

        if (!$object instanceof Concrete) {
            return null;
        }

        $linkGenerator = $object->getClass()->getLinkGenerator();
        if (!$linkGenerator instanceof LinkGeneratorInterface) {
            return null;
        }

        return $linkGenerator->generate($object, $this->options['arguments']);
    }
}
