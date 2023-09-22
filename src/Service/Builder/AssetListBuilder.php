<?php

namespace DsTrinityDataBundle\Service\Builder;

use DsTrinityDataBundle\DsTrinityDataEvents;
use DsTrinityDataBundle\Event\AssetListingQueryEvent;
use Doctrine\DBAL\Connection;
use Pimcore\Model\Asset;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AssetListBuilder implements DataBuilderInterface
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
            if ($asset = Asset::getById($id)) {
                yield $asset;
            }
        }
    }

    public function buildByIdList(int $id, array $options): ?ElementInterface
    {
        $list = $this->getList($options);

        $list->addConditionParam('id = ?', $id);
        $list->setLimit(1);

        $assets = $list->getAssets();

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

    protected function addAssetTypeRestriction(Asset\Listing $listing, ?array $allowedTypes): Asset\Listing
    {
        if ($allowedTypes === null) {
            $allowedTypes = array_filter(Asset::getTypes(), static function ($type) {
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
