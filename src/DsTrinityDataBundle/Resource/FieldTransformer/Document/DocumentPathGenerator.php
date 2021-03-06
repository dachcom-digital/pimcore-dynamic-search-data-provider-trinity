<?php

namespace DsTrinityDataBundle\Resource\FieldTransformer\Document;

use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use DynamicSearchBundle\Resource\FieldTransformerInterface;
use Pimcore\Model\Document;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentPathGenerator implements FieldTransformerInterface
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
        return false;
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
        $document = $resourceContainer->getResource();
        if (!$document instanceof Document) {
            return null;
        }

        return $document->getRealFullPath();
    }
}
