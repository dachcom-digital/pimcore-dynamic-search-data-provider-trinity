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
use DsTrinityDataBundle\Event\ObjectListingQueryEvent;
use Pimcore\Model\DataObject;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ObjectListBuilder implements DataBuilderInterface
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
            if ($object = DataObject::getById($id)) {
                yield $object;
            }
        }
    }

    public function buildByIdList(int $id, array $options): ?ElementInterface
    {
        $list = $this->getList($options);

        $list->addConditionParam('id = ?', $id);
        $list->setLimit(1);

        $objects = $list->getObjects();

        if (count($objects) === 0) {
            return null;
        }

        return $objects[0];
    }

    public function buildById(int $id): ?ElementInterface
    {
        return DataObject::getById($id);
    }

    protected function getList(array $options): DataObject\Listing
    {
        $allowedTypes = $options['object_types'];
        $allowedClasses = $options['object_class_names'];
        $limit = $options['object_limit'] ?? 0;
        $additionalParams = $options['object_additional_params'];

        $list = new DataObject\Listing();
        $list->setUnpublished(true);

        foreach ($additionalParams as $additionalParam => $additionalValue) {
            $list->addConditionParam($additionalParam, $additionalValue);
        }

        if ($limit > 0) {
            $list->setLimit($limit);
        }

        $this->addObjectTypeRestriction($list, $allowedTypes);
        $this->addClassNameRestriction($list, $allowedClasses);

        $event = new ObjectListingQueryEvent($list, $options);
        $this->eventDispatcher->dispatch($event, DsTrinityDataEvents::LISTING_QUERY_OBJECTS);

        return $event->getListing();
    }

    protected function addObjectTypeRestriction(DataObject\Listing $listing, ?array $allowedTypes): DataObject\Listing
    {
        if ($allowedTypes === null) {
            $allowedTypes = array_filter(DataObject::getTypes(), static function ($type) {
                return $type !== 'folder';
            });
        }

        if (count($allowedTypes) === 0) {
            return $listing;
        }

        $listing->setObjectTypes($allowedTypes);

        return $listing;
    }

    protected function addClassNameRestriction(DataObject\Listing $listing, array $allowedClasses): DataObject\Listing
    {
        if (count($allowedClasses) === 0) {
            return $listing;
        }

        $quotedClassNames = [];
        foreach ($allowedClasses as $cName) {
            $quotedClassNames[] = $this->db->quote($cName);
        }

        $listing->addConditionParam(sprintf('className IN (%s)', implode(',', $quotedClassNames)), '');

        return $listing;
    }
}
