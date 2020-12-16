<?php

namespace DsTrinityDataBundle\Event;

use Pimcore\Model\DataObject;
use Symfony\Contracts\EventDispatcher\Event;

class ObjectListingQueryEvent extends Event
{
    /**
     * @var DataObject\Listing
     */
    protected $listing;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param DataObject\Listing $listing
     * @param array              $options
     */
    public function __construct(DataObject\Listing $listing, array $options)
    {
        $this->listing = $listing;
        $this->options = $options;
    }

    /**
     * @return DataObject\Listing
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
