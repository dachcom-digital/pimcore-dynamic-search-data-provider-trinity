<?php

namespace DsTrinityDataBundle\Service\Builder;

use DsTrinityDataBundle\DsTrinityDataEvents;
use DsTrinityDataBundle\Event\AssetListingQueryEvent;
use Pimcore\Db\Connection;
use Pimcore\Model\Asset;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AssetListBuilder implements DataBuilderInterface
{
    protected Connection $db;
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(Connection $db, EventDispatcherInterface $eventDispatcher)
    {
        $this->db = $db;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function buildByList(array $options): array
    {
        $list = $this->getList($options);

        return $list->getAssets();
    }

    public function buildByIdList(int $id, array $options): ?ElementInterface
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

    public function buildById(int $id): ?ElementInterface
    {
        return Asset::getById($id);
    }

    protected function getList(array $options): Asset\Listing
    {
        $allowedTypes = $options['asset_types'];
        $limit = $options['asset_limit'] ?? 0;
        $additionalParams = $options['asset_additional_params'];

        $list = new Asset\Listing();

        foreach ($additionalParams as $additionalParam => $additionalValue) {
            $list->addConditionParam($additionalParam, $additionalValue);
        }

        if ($limit > 0) {
            $list->setLimit($limit);
        }

        $this->addAssetTypeRestriction($list, $allowedTypes);

        $event = new AssetListingQueryEvent($list, $options);
        $this->eventDispatcher->dispatch($event, DsTrinityDataEvents::LISTING_QUERY_ASSETS);

        return $list;
    }

    protected function addAssetTypeRestriction(Asset\Listing $listing, array $allowedTypes): Asset\Listing
    {
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
