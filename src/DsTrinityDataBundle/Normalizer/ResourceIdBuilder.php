<?php

namespace DsTrinityDataBundle\Normalizer;

use DynamicSearchBundle\Manager\TransformerManagerInterface;
use DynamicSearchBundle\Normalizer\ResourceIdBuilderInterface;
use DynamicSearchBundle\Transformer\Container\DocumentContainerInterface;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document\Page;
use Pimcore\Model\Element\ElementInterface;

class ResourceIdBuilder implements ResourceIdBuilderInterface
{
    /**
     * @var TransformerManagerInterface
     */
    protected $transformerManager;

    /**
     * @var array
     */
    protected $normalizerOptions;

    /**
     * @param TransformerManagerInterface $transformerManager
     */
    public function __construct(TransformerManagerInterface $transformerManager)
    {
        $this->transformerManager = $transformerManager;
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions(array $normalizerOptions)
    {
        $this->normalizerOptions = $normalizerOptions;
    }

    /**
     * {@inheritDoc}
     */
    public function build(DocumentContainerInterface $documentContainer, array $buildOptions = [])
    {
        if ($documentContainer->getResource() instanceof ElementInterface) {
            return $this->buildFromPimcoreResource($documentContainer->getResource(), $buildOptions);
        }

        return null;
    }

    /**
     * @param ElementInterface $resource
     * @param array            $buildOptions
     *
     * @return string|null
     */
    protected function buildFromPimcoreResource(ElementInterface $resource, array $buildOptions)
    {
        $locale = isset($buildOptions['locale']) ? $buildOptions['locale'] : null;
        $documentType = null;
        $id = null;

        if ($resource instanceof DataObject) {
            $documentType = 'object';
            $id = $resource->getId();
        } elseif ($resource instanceof Page) {
            $documentType = 'page';
            $id = $resource->getId();
        }

        if ($documentType === null) {
            return null;
        }

        if ($locale !== null) {
            return sprintf('%s_%s_%d', $documentType, $locale, $id);
        }

        return sprintf('%s_%d', $documentType, $id);

    }
}
