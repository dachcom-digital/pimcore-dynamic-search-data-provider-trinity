<?php

namespace DsTrinityDataBundle\Service\Builder;

use Pimcore\Model\Asset;

class AssetListBuilder implements DataBuilderInterface
{
    /**
     * {@inheritDoc}
     */
    public function buildByList(array $options): array
    {
        $allowedTypes = $options['asset_types'];
        $limit = $options['asset_limit'];
        $additionalParams = $options['asset_additional_params'];

        $list = new Asset\Listing();

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
     * {@inheritDoc}
     */
    public function buildById(int $id)
    {
        return Asset::getById($id);
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