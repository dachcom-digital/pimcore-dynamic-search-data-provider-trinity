<?php

namespace DsTrinityDataBundle\Service;

use DynamicSearchBundle\Logger\LoggerInterface;

interface DataProviderServiceInterface
{
    public function setLogger(LoggerInterface $logger);

    public function setContextName(string $contextName);

    public function setContextDispatchType(string $dispatchType);

    public function setIndexOptions(array $indexOptions);

    public function setRuntimeValues(array $runtimeValues);

    public function fetchIndexData();

    public function fetchInsertData();

    public function fetchUpdateData();
}