<?php

namespace DsTrinityDataBundle\Normalizer;

use DynamicSearchBundle\Context\ContextDefinitionInterface;
use DynamicSearchBundle\Exception\NormalizerException;
use DynamicSearchBundle\Normalizer\Resource\NormalizedDataResource;
use DynamicSearchBundle\Normalizer\Resource\ResourceMeta;
use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocalizedResourceNormalizer extends AbstractResourceNormalizer
{
    /**
     * @var array
     */
    protected $options;

    /**
     * {@inheritdoc}
     */
    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locales', 'skip_not_localized_documents']);
        $resolver->setAllowedTypes('locales', ['string[]', 'null']);
        $resolver->setAllowedTypes('skip_not_localized_documents', ['bool']);
        $resolver->setDefaults(['skip_not_localized_documents' => true]);
        $resolver->setDefaults(['locales' => null]);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param ContextDefinitionInterface $contextDefinition
     * @param ResourceContainerInterface $resourceContainer
     *
     * @return array
     *
     * @throws NormalizerException
     */
    protected function normalizeDocument(ContextDefinitionInterface $contextDefinition, ResourceContainerInterface $resourceContainer)
    {
        /** @var Document $document */
        $document = $resourceContainer->getResource();

        if (!method_exists($document, 'getProperty')) {
            return [];
        }

        // @todo: Hardlink data detection!
        // @todo: Related document detection! (some content parts could be inherited)
        // @todo: How to handle Snippets?

        $documentLocale = $document->getProperty('language');
        if (empty($documentLocale)) {
            if ($this->options['skip_not_localized_documents'] === false) {
                throw new NormalizerException(sprintf('Cannot determinate locale aware document id "%s": no language property given.', $document->getId()));
            } else {
                return [];
            }
        }

        $documentId = sprintf('%s_%s_%d', 'document', $documentLocale, $document->getId());
        $resourceMeta = new ResourceMeta($documentId, $document->getId(), 'document', $document->getType(), null, [], ['locale' => $documentLocale]);
        $returnResourceContainer = $contextDefinition->getContextDispatchType() === ContextDefinitionInterface::CONTEXT_DISPATCH_TYPE_DELETE ? null : $resourceContainer;

        return [new NormalizedDataResource($returnResourceContainer, $resourceMeta)];
    }

    /**
     * @param ContextDefinitionInterface $contextDefinition
     * @param ResourceContainerInterface $resourceContainer
     *
     * @return array
     */
    protected function normalizeAsset(ContextDefinitionInterface $contextDefinition, ResourceContainerInterface $resourceContainer)
    {
        /** @var Asset $asset */
        $asset = $resourceContainer->getResource();

        $documentId = sprintf('%s_%d', 'asset', $asset->getId());
        $resourceMeta = new ResourceMeta($documentId, $asset->getId(), 'asset', $asset->getType(), null, [], ['locale' => null]);
        $returnResourceContainer = $contextDefinition->getContextDispatchType() === ContextDefinitionInterface::CONTEXT_DISPATCH_TYPE_DELETE ? null : $resourceContainer;

        return [new NormalizedDataResource($returnResourceContainer, $resourceMeta)];
    }

    /**
     * @param ContextDefinitionInterface $contextDefinition
     * @param ResourceContainerInterface $resourceContainer
     *
     * @return array
     */
    protected function normalizeDataObject(ContextDefinitionInterface $contextDefinition, ResourceContainerInterface $resourceContainer)
    {
        /** @var DataObject\Concrete $object */
        $object = $resourceContainer->getResource();

        $normalizedResources = [];
        foreach ($this->getLocales() as $locale) {
            $documentId = sprintf('%s_%s_%d', 'object', $locale, $object->getId());
            $resourceMeta = new ResourceMeta($documentId, $object->getId(), 'object', $object->getType(), $object->getClassName(), [], ['locale' => $locale]);
            $returnResourceContainer = $contextDefinition->getContextDispatchType() === ContextDefinitionInterface::CONTEXT_DISPATCH_TYPE_DELETE ? null : $resourceContainer;
            $normalizedResources[] = new NormalizedDataResource($returnResourceContainer, $resourceMeta);
        }

        return $normalizedResources;
    }

    /**
     * @return string[]
     */
    protected function getLocales()
    {
        if($this->options['locales'] === null) {
            return \Pimcore\Tool::getValidLanguages();
        }

        return $this->options['locales'];
    }
}
