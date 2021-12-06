<?php

namespace DsTrinityDataBundle\Resource\FieldTransformer\Asset;

use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use DynamicSearchBundle\Resource\FieldTransformerInterface;
use Pimcore\Model\Asset;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssetMetaExtractor implements FieldTransformerInterface
{
    protected array $options;

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['name', 'locale']);
        $resolver->setAllowedTypes('name', ['string']);
        $resolver->setAllowedTypes('locale', ['string', 'null']);
        $resolver->setDefaults([
            'clean_string' => true
        ]);
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @throws \Exception
     */
    public function transformData(string $dispatchTransformerName, ResourceContainerInterface $resourceContainer): mixed
    {
        $asset = $resourceContainer->getResource();
        if (!$asset instanceof Asset) {
            return null;
        }

        if (!$asset->getHasMetaData()) {
            return null;
        }

        $metaData = $asset->getMetadata($this->options['name'], $this->options['locale']);

        if ($this->options['clean_string'] === true) {
            $metaData = trim(preg_replace('/\s+/', ' ', strip_tags($metaData)));
        }

        return $metaData;
    }
}
