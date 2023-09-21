<?php

namespace DsTrinityDataBundle\Event;

use Pimcore\Model\Document;
use Symfony\Contracts\EventDispatcher\Event;

class DocumentListingQueryEvent extends Event
{
    public function __construct(protected Document\Listing $listing, protected array $options)
    {
    }

    public function getListing(): Document\Listing
    {
        return $this->listing;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
