<?php

namespace DsTrinityDataBundle\Event;

use Pimcore\Model\DataObject;
use Symfony\Contracts\EventDispatcher\Event;

class ObjectListingQueryEvent extends Event
{
    public function __construct(protected DataObject\Listing $listing, protected array $options)
    {
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
