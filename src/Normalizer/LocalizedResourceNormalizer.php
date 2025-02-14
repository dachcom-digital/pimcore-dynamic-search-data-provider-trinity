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
    protected array $options;

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['locales', 'skip_not_localized_documents']);
        $resolver->setAllowedTypes('locales', ['string[]', 'null']);
        $resolver->setAllowedTypes('skip_not_localized_documents', ['bool']);
        $resolver->setDefaults(['skip_not_localized_documents' => true]);
        $resolver->setDefaults(['locales' => null]);
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @throws NormalizerException
     */
    protected function normalizeDocument(ContextDefinitionInterface $contextDefinition, ResourceContainerInterface $resourceContainer): array
    {
        $document = $resourceContainer->getResource();

        if (!$document instanceof Document) {
            return [];
        }

        // @todo: Hardlink data detection!
        // @todo: Related document detection! (some content parts could be inherited)
        // @todo: How to handle Snippets?

        $documentLocale = $document->getProperty('language');

        if (empty($documentLocale)) {
            if ($this->options['skip_not_localized_documents'] === false) {
                throw new NormalizerException(sprintf('Cannot determinate locale aware document id "%s": no language property given.', $document->getId()));
            }

            return [];
        }

        $documentId = sprintf('%s_%s_%d', 'document', $documentLocale, $document->getId());
        $resourceMeta = new ResourceMeta($documentId, $document->getId(), 'document', $document->getType(), null, [], ['locale' => $documentLocale]);
        $returnResourceContainer = $contextDefinition->getContextDispatchType() === ContextDefinitionInterface::CONTEXT_DISPATCH_TYPE_DELETE ? null : $resourceContainer;

        return [new NormalizedDataResource($returnResourceContainer, $resourceMeta)];
    }

    protected function normalizeAsset(ContextDefinitionInterface $contextDefinition, ResourceContainerInterface $resourceContainer): array
    {
        /** @var Asset $asset */
        $asset = $resourceContainer->getResource();

        $documentId = sprintf('%s_%d', 'asset', $asset->getId());
        $resourceMeta = new ResourceMeta($documentId, $asset->getId(), 'asset', $asset->getType(), null, [], ['locale' => null]);
        $returnResourceContainer = $contextDefinition->getContextDispatchType() === ContextDefinitionInterface::CONTEXT_DISPATCH_TYPE_DELETE ? null : $resourceContainer;

        return [new NormalizedDataResource($returnResourceContainer, $resourceMeta)];
    }

    protected function normalizeDataObject(ContextDefinitionInterface $contextDefinition, ResourceContainerInterface $resourceContainer): array
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

    protected function getLocales(): array
    {
        if ($this->options['locales'] === null) {
            return \Pimcore\Tool::getValidLanguages();
        }

        return $this->options['locales'];
    }
}
