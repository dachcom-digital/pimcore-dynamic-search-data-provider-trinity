<?php

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
    /**
     * @var array
     */
    protected $options;

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
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
