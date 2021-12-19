<?php

namespace DsTrinityDataBundle\Event;

use Pimcore\Model\Asset;
use Symfony\Contracts\EventDispatcher\Event;

class AssetListingQueryEvent extends Event
{
    protected Asset\Listing $listing;

    protected array $options;

    public function __construct(Asset\Listing $listing, array $options)
    {
        $this->listing = $listing;
        $this->options = $options;
    }

    public function getListing(): Asset\Listing
    {
        return $this->listing;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
