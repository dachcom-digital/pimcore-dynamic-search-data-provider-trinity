<?php

namespace DsTrinityDataBundle\Service\Builder;

use Pimcore\Model\DataObject;

class ObjectListBuilder implements DataBuilderInterface
{
    /**
     * {@inheritDoc}
     */
    public function build(array $options): array
    {
        $id = $options['id'];
        $allowedTypes = $options['object_types'];
        $allowedClasses = $options['object_class_names'];
        $includeUnpublished = $options['object_ignore_unpublished'] === false;
        $limit = $options['object_limit'];
        $additionalParams = $options['object_additional_params'];

        $list = new DataObject\Listing();

        if ($includeUnpublished === true) {
            $list->setUnpublished(true);
        }

        if ($id !== null) {
            $list->addConditionParam('o_id = ?', $id);
        }

        foreach ($additionalParams as $additionalParam => $additionalValue) {
            $list->addConditionParam($additionalParam, $additionalValue);
        }

        if ($limit > 0) {
            $list->setLimit($limit);
        }

        $this->addObjectTypeRestriction($list, $allowedTypes);
        $this->addClassNameRestriction($list, $allowedClasses);

        return $list->getObjects();
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
            $quotedClassNames[] = \Pimcore\Db::get()->quote($cName);
        }

        $listing->addConditionParam(sprintf('o_className in(%s)', implode(',', $quotedClassNames)), '');

        return $listing;
    }
}