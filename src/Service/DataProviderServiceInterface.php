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

namespace DsTrinityDataBundle\Service;

use DynamicSearchBundle\Normalizer\Resource\ResourceMetaInterface;
use Pimcore\Model\Element\ElementInterface;

interface DataProviderServiceInterface
{
    public function setContextName(string $contextName): void;

    public function setContextDispatchType(string $dispatchType): void;

    public function setIndexOptions(array $indexOptions): void;

    public function validate(ElementInterface $resource): bool;

    public function fetchListData(): void;

    public function fetchSingleData(ResourceMetaInterface $resourceMeta): void;
}
