<?php

namespace DsTrinityDataBundle\Transformer;

use DynamicSearchBundle\Context\ContextDataInterface;
use DynamicSearchBundle\Logger\LoggerInterface;
use DynamicSearchBundle\Transformer\Container\DataContainer;
use DynamicSearchBundle\Transformer\Container\DataContainerInterface;
use DynamicSearchBundle\Transformer\DispatchTransformerInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;

class TrinityDataTransformer implements DispatchTransformerInterface
{
    /**
     * @var ContextDataInterface
     */
    protected $contextData;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * {@inheritDoc}
     */
    public function isApplicable($data): bool
    {
        if ($data instanceof Asset) {
            return true;
        } elseif ($data instanceof Document) {
            return true;
        } elseif ($data instanceof DataObject\Concrete) {
            return true;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function transformData(ContextDataInterface $contextData, $data): ?DataContainerInterface
    {
        $this->contextData = $contextData;

        $type = null;
        $dataType = null;

        if ($data instanceof Asset) {
            $type = 'asset';
            $dataType = $data->getType();
        } elseif ($data instanceof Document) {
            $type = 'document';
            $dataType = $data->getType();
        } elseif ($data instanceof DataObject\Concrete) {
            $type = 'object';
            $dataType = $data->getType();
        }

        return new DataContainer([
            'type'      => $type,
            'data_type' => $dataType,
            'data'      => $data
        ]);
    }

    /**
     * @param string $level
     * @param string $message
     */
    protected function log($level, $message)
    {
        $this->logger->log($level, $message, 'trinity_data_transformer', $this->contextData->getName());
    }
}