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

namespace DsTrinityDataBundle\Service\Builder;

use Pimcore\Model\Element\ElementInterface;

interface DataBuilderInterface
{
    public function buildByList(array $options): \Generator;

    public function buildByIdList(int $id, array $options): ?ElementInterface;

    /**
     * This method does not validate if element is allowed to be indexed.
     * This has to be done via buildByList or buildByIdList.
     */
    public function buildById(int $id): ?ElementInterface;
}
