<?php

namespace DsTrinityDataBundle\Event;

use Pimcore\Model\Asset;
use Symfony\Component\EventDispatcher\Event;

class AssetListingQueryEvent extends Event
{
    /**
     * @var Asset\Listing
     */
    protected $listing;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param Asset\Listing $listing
     * @param array         $options
     */
    public function __construct(Asset\Listing $listing, array $options)
    {
        $this->listing = $listing;
        $this->options = $options;
    }

    /**
     * @return Asset\Listing
     */
    public function getListing()
    {
        return $this->listing;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
