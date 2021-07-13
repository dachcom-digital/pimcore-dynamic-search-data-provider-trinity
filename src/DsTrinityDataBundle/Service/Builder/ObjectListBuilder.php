<?php

namespace DsTrinityDataBundle\Service\Builder;

use DsTrinityDataBundle\DsTrinityDataEvents;
use DsTrinityDataBundle\Event\ObjectListingQueryEvent;
use Pimcore\Db\Connection;
use Pimcore\Model\DataObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ObjectListBuilder implements DataBuilderInterface
{
    /**
     * @var Connection
     */
    protected $db;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param Connection               $db
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Connection $db, EventDispatcherInterface $eventDispatcher)
    {
        $this->db = $db;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function buildByList(array $options): \Generator
    {
        $list = $this->getList($options);

        $idList = $list->loadIdList();

        foreach ($idList as $id) {
            if ($object = DataObject::getById($id)) {
                yield $object;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildByIdList(int $id, array $options)
    {
        $list = $this->getList($options);

        $list->addConditionParam('o_id = ?', $id);
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

    /**
     * {@inheritdoc}
     */
    public function buildById(int $id)
    {
        return DataObject::getById($id);
    }

    /**
     * @param array $options
     *
     * @return DataObject\Listing
     */
    protected function getList(array $options)
    {
        $allowedTypes = $options['object_types'];
        $allowedClasses = $options['object_class_names'];
        $includeUnpublished = $options['object_ignore_unpublished'] === false;
        $limit = $options['object_limit'];
        $additionalParams = $options['object_additional_params'];

        $list = new DataObject\Listing();

        if ($includeUnpublished === true) {
            $list->setUnpublished(true);
        }

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

    /**
     * @param DataObject\Listing $listing
     * @param array              $allowedTypes
     *
     * @return DataObject\Listing
     */
    protected function addObjectTypeRestriction(DataObject\Listing $listing, array $allowedTypes)
    {
        if (count($allowedTypes) === 0) {
            return $listing;
        }

        $listing->setObjectTypes($allowedTypes);

        return $listing;
    }

    /**
     * @param DataObject\Listing $listing
     * @param array              $allowedClasses
     *
     * @return DataObject\Listing
     */
    protected function addClassNameRestriction(DataObject\Listing $listing, array $allowedClasses)
    {
        if (count($allowedClasses) === 0) {
            return $listing;
        }

        $quotedClassNames = [];
        foreach ($allowedClasses as $cName) {
            $quotedClassNames[] = $this->db->quote($cName);
        }

        $listing->addConditionParam(sprintf('o_className IN (%s)', implode(',', $quotedClassNames)), '');

        return $listing;
    }
}
