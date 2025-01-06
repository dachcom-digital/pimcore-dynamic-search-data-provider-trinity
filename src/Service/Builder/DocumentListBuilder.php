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

namespace DsTrinityDataBundle\Service\Builder;

use Doctrine\DBAL\Connection;
use DsTrinityDataBundle\DsTrinityDataEvents;
use DsTrinityDataBundle\Event\DocumentListingQueryEvent;
use Pimcore\Model\Document;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DocumentListBuilder implements DataBuilderInterface
{
    public function __construct(
        protected Connection $db,
        protected EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function buildByList(array $options): \Generator
    {
        $list = $this->getList($options);

        foreach ($list->loadIdList() as $id) {
            if ($doc = Document::getById($id)) {
                yield $doc;
            }
        }
    }

    public function buildByIdList(int $id, array $options): ?ElementInterface
    {
        $list = $this->getList($options);

        $list->addConditionParam('id = ?', $id);
        $list->setLimit(1);

        $documents = $list->getDocuments();

        if (count($documents) === 0) {
            return null;
        }

        return $documents[0];
    }

    public function buildById(int $id): ?ElementInterface
    {
        return Document::getById($id);
    }

    protected function getList(array $options): Document\Listing
    {
        $allowedTypes = $options['document_types'];
        $limit = $options['document_limit'] ?? 0;
        $additionalParams = $options['document_additional_params'];

        $list = new Document\Listing();
        $list->setUnpublished(true);

        foreach ($additionalParams as $additionalParam => $additionalValue) {
            $list->addConditionParam($additionalParam, $additionalValue);
        }

        if ($limit > 0) {
            $list->setLimit($limit);
        }

        $this->addDocumentTypeRestriction($list, $allowedTypes);

        $event = new DocumentListingQueryEvent($list, $options);
        $this->eventDispatcher->dispatch($event, DsTrinityDataEvents::LISTING_QUERY_DOCUMENTS);

        return $list;
    }

    protected function addDocumentTypeRestriction(Document\Listing $listing, ?array $allowedTypes): Document\Listing
    {
        if ($allowedTypes === null) {
            $allowedTypes = array_filter(Document::getTypes(), static function ($type) {
                return $type !== 'folder';
            });
        }

        if (count($allowedTypes) === 0) {
            return $listing;
        }

        $quotedTypes = [];
        foreach ($allowedTypes as $cName) {
            $quotedTypes[] = $this->db->quote($cName);
        }

        $listing->addConditionParam(sprintf('type in(%s)', implode(',', $quotedTypes)), '');

        return $listing;
    }
}
