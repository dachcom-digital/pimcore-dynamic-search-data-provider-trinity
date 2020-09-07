<?php

namespace DsTrinityDataBundle\Service;

use DynamicSearchBundle\Normalizer\Resource\ResourceMetaInterface;
use DynamicSearchBundle\Resource\Proxy\ProxyResourceInterface;
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
     * @return ProxyResourceInterface|null
     */
    public function checkResourceProxy(ElementInterface $resource);

    /**
     * This method only gets executed on untrusted events like insert, update or delete
     *
     * @param ElementInterface $resource
     *
     * @return bool
     */
    public function validate(ElementInterface $resource);

    public function fetchListData();

    /**
     * @param ResourceMetaInterface $resourceMeta
     */
    public function fetchSingleData(ResourceMetaInterface $resourceMeta);
}
