<?php

namespace DsTrinityDataBundle\Resource\FieldTransformer\Asset;

use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use DynamicSearchBundle\Resource\FieldTransformerInterface;
use Pimcore\Model\Asset;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssetMetaExtractor implements FieldTransformerInterface
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
        $resolver->setRequired(['name', 'locale']);
        $resolver->setAllowedTypes('name', ['string']);
        $resolver->setAllowedTypes('locale', ['string', 'null']);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**+
     * @param string                     $dispatchTransformerName
     * @param ResourceContainerInterface $resourceContainer
     *
     * @return int|mixed|null
     * @throws \Exception
     */
    public function transformData(string $dispatchTransformerName, ResourceContainerInterface $resourceContainer)
    {
        $asset = $resourceContainer->getResource();
        if (!$asset instanceof Asset) {
            return null;
        }

        if(!$asset->getHasMetaData()) {
            return null;
        }

        $metaData = $asset->getMetadata($this->options['name'], $this->options['locale']);

        return $metaData;

    }
}
