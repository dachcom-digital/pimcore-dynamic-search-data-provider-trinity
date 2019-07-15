<?php

namespace DsTrinityDataBundle\Service\Builder;

use Pimcore\Model\Asset;

class AssetListBuilder implements DataBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildByList(array $options): array
    {
        $list = $this->getList($options);

        return $list->getAssets();
    }

    /**
     * {@inheritdoc}
     */
    public function buildByIdList(int $id, array $options)
    {
        $list = $this->getList($options);

        $list->addConditionParam('id = ?', $id);
        $list->setLimit(1);

        $assets = $list->getAssets();

        if (!is_array($assets)) {
            return null;
        }

        if (count($assets) === 0) {
            return null;
        }

        return $assets[0];
    }

    /**
     * {@inheritdoc}
     */
    public function buildById(int $id)
    {
        return Asset::getById($id);
    }

    /**
     * @param array $options
     *
     * @return Asset\Listing
     */
    protected function getList(array $options)
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

        return $list;
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
