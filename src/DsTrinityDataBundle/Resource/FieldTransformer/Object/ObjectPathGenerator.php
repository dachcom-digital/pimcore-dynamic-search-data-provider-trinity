<?php

namespace DsTrinityDataBundle\Resource\FieldTransformer\Object;

use CoreShop\Component\Pimcore\Routing\LinkGeneratorInterface;
use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use DynamicSearchBundle\Resource\FieldTransformerInterface;
use Pimcore\Model\DataObject\AbstractObject;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ObjectPathGenerator implements FieldTransformerInterface
{
    /**
     * @var LinkGeneratorInterface
     */
    protected $linkGenerator;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param LinkGeneratorInterface $linkGenerator
     */
    public function __construct(LinkGeneratorInterface $linkGenerator)
    {
        $this->linkGenerator = $linkGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'arguments',
            'route_name',
            'fetch_object_for_variant',
            'reference_type'
        ]);

        $resolver->setAllowedTypes('arguments', ['array']);
        $resolver->setAllowedTypes('fetch_object_for_variant', ['bool']);
        $resolver->setAllowedTypes('reference_type', ['int']);
        $resolver->setAllowedTypes('route_name', ['string']);

        $resolver->setDefaults([
            'arguments'                => [],
            'fetch_object_for_variant' => false,
            'reference_type'           => UrlGeneratorInterface::RELATIVE_PATH
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
        if (!$object instanceof AbstractObject) {
            return null;
        }

        if ($this->options['fetch_object_for_variant'] === true && $object->getType() === AbstractObject::OBJECT_TYPE_VARIANT) {
            while ($object->getType() === AbstractObject::OBJECT_TYPE_VARIANT) {
                $object = $object->getParent();
            }
        }

        return $this->linkGenerator->generate($object, $this->options['route_name'], $this->options['arguments'], $this->options['reference_type']);
    }
}
