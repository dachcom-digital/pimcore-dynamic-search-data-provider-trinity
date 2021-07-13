<?php

namespace DsTrinityDataBundle\Service\Builder;

use DsTrinityDataBundle\DsTrinityDataEvents;
use DsTrinityDataBundle\Event\AssetListingQueryEvent;
use Pimcore\Db\Connection;
use Pimcore\Model\Asset;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AssetListBuilder implements DataBuilderInterface
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

        $c = 0;
        foreach ($list->loadIdList() as $id) {
            if ($asset = Asset::getById($id)) {
                yield $asset;
            }

            // call the garbage collector if memory consumption is > 100MB
            if (memory_get_usage() > 100000000 && ($c % 300 === 0)) {
                \Pimcore::collectGarbage();
            }

            $c++;
        }
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

        $event = new AssetListingQueryEvent($list, $options);
        $this->eventDispatcher->dispatch($event, DsTrinityDataEvents::LISTING_QUERY_ASSETS);

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
            $quotedTypes[] = $this->db->quote($cName);
        }

        $listing->addConditionParam(sprintf('type in(%s)', implode(',', $quotedTypes)), '');

        return $listing;
    }
}
