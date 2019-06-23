<?php

namespace DsTrinityDataBundle\Service\Builder;

interface DataBuilderInterface
{
    /**
     * @param array $options
     *
     * @return array
     */
    public function build(array $options): array;
}