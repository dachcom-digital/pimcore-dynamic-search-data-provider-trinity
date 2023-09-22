<?php

namespace DsTrinityDataBundle\Service\Builder;

use DsTrinityDataBundle\DsTrinityDataEvents;
use DsTrinityDataBundle\Event\ObjectListingQueryEvent;
use Doctrine\DBAL\Connection;
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

    public function buildByList(array $options): array
    {
        $list = $this->getList($options);

        return $list->getObjects();
    }

    public function buildByIdList(int $id, array $options): ?ElementInterface
    {
        $list = $this->getList($options);

        $list->addConditionParam('id = ?', $id);
        $list->setLimit(1);

        $objects = $list->getObjects();

        if (!is_array($objects)) {
            return null;
        }

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
