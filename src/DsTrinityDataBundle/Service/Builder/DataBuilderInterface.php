<?php

namespace DsTrinityDataBundle\Service\Builder;

use Pimcore\Model\Element\ElementInterface;

interface DataBuilderInterface
{
    /**
     * @param array $options
     *
     * @return array
     */
    public function buildByList(array $options): array;

    /**
     * @param int $id
     *
     * @return ElementInterface|null
     */
    public function buildById(int $id);
}