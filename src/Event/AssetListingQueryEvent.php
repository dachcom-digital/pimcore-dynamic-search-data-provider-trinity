<?php

namespace DsTrinityDataBundle\Event;

use Pimcore\Model\Asset;
use Symfony\Contracts\EventDispatcher\Event;

class AssetListingQueryEvent extends Event
{
    public function __construct(protected Asset\Listing $listing, protected array $options)
    {
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
