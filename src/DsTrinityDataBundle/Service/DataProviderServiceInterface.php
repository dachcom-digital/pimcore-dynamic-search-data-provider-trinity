<?php

namespace DsTrinityDataBundle\Service;

use DynamicSearchBundle\Normalizer\Resource\ResourceMetaInterface;

interface DataProviderServiceInterface
{
    /**
     * @param string $contextName
     */
    public function setContextName(string $contextName);

    /**
     * @param string $dispatchType
     */
    public function setContextDispatchType(string $dispatchType);

    /**
     * @param array $indexOptions
     */
    public function setIndexOptions(array $indexOptions);

    /**
     * @return void
     */
    public function fetchListData();

    /**
     * @param ResourceMetaInterface $resourceMeta
     */
    public function fetchSingleData(ResourceMetaInterface $resourceMeta);

}