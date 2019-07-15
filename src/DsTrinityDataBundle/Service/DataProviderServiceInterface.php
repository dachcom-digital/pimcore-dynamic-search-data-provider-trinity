<?php

namespace DsTrinityDataBundle\Service;

use DynamicSearchBundle\Normalizer\Resource\ResourceMetaInterface;
use Pimcore\Model\Element\ElementInterface;

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
     * @param ElementInterface $resource
     *
     * @return bool
     */
    public function validate($resource);

    public function fetchListData();

    /**
     * @param ResourceMetaInterface $resourceMeta
     */
    public function fetchSingleData(ResourceMetaInterface $resourceMeta);
}
