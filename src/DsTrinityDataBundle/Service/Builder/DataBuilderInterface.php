<?php

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
