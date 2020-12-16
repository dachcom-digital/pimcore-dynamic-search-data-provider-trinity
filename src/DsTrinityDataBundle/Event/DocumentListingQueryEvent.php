<?php

namespace DsTrinityDataBundle\Event;

use Pimcore\Model\Document;
use Symfony\Contracts\EventDispatcher\Event;

class DocumentListingQueryEvent extends Event
{
    /**
     * @var Document\Listing
     */
    protected $listing;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param Document\Listing $listing
     * @param array            $options
     */
    public function __construct(Document\Listing $listing, array $options)
    {
        $this->listing = $listing;
        $this->options = $options;
    }

    /**
     * @return Document\Listing
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
