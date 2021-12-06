<?php

namespace DsTrinityDataBundle\Service;

use DynamicSearchBundle\Normalizer\Resource\ResourceMetaInterface;
use Pimcore\Model\Element\ElementInterface;

interface DataProviderServiceInterface
{
    public function setContextName(string $contextName): void;

    public function setContextDispatchType(string $dispatchType): void;

    public function setIndexOptions(array $indexOptions): void;

    /**
     * @deprecated since 1.0.0 and will be removed in 2.0.0
     */
    public function checkResourceProxy(ElementInterface $resource);

    public function validate(ElementInterface $resource): bool;

    public function fetchListData(): void;

    public function fetchSingleData(ResourceMetaInterface $resourceMeta): void;
}
