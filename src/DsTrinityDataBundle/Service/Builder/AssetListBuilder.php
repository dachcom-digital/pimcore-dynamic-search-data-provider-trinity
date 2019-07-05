<?php

namespace DsTrinityDataBundle\Service\Builder;

use Pimcore\Model\Asset;

class AssetListBuilder implements DataBuilderInterface
{
    /**
     * {@inheritDoc}
     */
    public function build(array $options): array
    {
        $id = $options['id'];
        $allowedTypes = $options['asset_types'];
        $limit = $options['asset_limit'];
        $additionalParams = $options['asset_additional_params'];

        $list = new Asset\Listing();

        if ($id !== null) {
            $list->addConditionParam('id = ?', $id);
        }

        foreach ($additionalParams as $additionalParam => $additionalValue) {
            $list->addConditionParam($additionalParam, $additionalValue);
        }

        if ($limit > 0) {
            $list->setLimit($limit);
        }

        $this->addAssetTypeRestriction($list, $allowedTypes);

        return $list->getAssets();
    }

    /**
     * @param Asset\Listing $listing
     * @param array         $allowedTypes
     *
     * @return Asset\Listing
     */
    protected function addAssetTypeRestriction(Asset\Listing $listing, array $allowedTypes)
    {
        if (count($allowedTypes) === 0) {
            return $listing;
        }

        $quotedTypes = [];
        foreach ($allowedTypes as $cName) {
            $quotedTypes[] = \Pimcore\Db::get()->quote($cName);
        }

        $listing->addConditionParam(sprintf('type in(%s)', implode(',', $quotedTypes)), '');

        return $listing;
    }
}