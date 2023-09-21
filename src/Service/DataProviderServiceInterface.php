<?php

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
