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
     * @param ElementInterface $resource
     *
     * @return ProxyResourceInterface|null
     *
     * @deprecated since 1.0.0 and will be removed in 2.0.0
     */
    public function checkResourceProxy(ElementInterface $resource);

    /**
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
