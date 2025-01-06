<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

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
