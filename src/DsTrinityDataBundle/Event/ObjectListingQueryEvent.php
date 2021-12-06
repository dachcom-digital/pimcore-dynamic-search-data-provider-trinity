<?php

namespace DsTrinityDataBundle\Event;

use Pimcore\Model\DataObject;
use Symfony\Contracts\EventDispatcher\Event;

class ObjectListingQueryEvent extends Event
{
    protected DataObject\Listing $listing;
    protected array $options;

    public function __construct(DataObject\Listing $listing, array $options)
    {
        $this->listing = $listing;
        $this->options = $options;
    }

    public function getListing(): DataObject\Listing
    {
        return $this->listing;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
