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
     * This method only gets executed on untrusted events like insert, update or delete
     *
     * @param ElementInterface $resource
     *
     * @return ElementInterface
     */
    public function checkResourceProxy($resource);

    /**
     * This method only gets executed on untrusted events like insert, update or delete
     *
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
