<?php

namespace DsTrinityDataBundle\Registry;

use DsTrinityDataBundle\Service\Builder\DataBuilderInterface;

class DataBuilderRegistry implements DataBuilderRegistryInterface
{
    /**
     * @var array|DataBuilderInterface[]
     */
    protected $builder;

    /**
     * @param DataBuilderInterface $service
     * @param array                $identifier
     * @param array                $type
     */
    public function register($service, $identifier, $type)
    {
        if (!is_string($type)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to define a valid builder type.', get_class($service))
            );
        }

        if (!in_array($type, ['document', 'asset', 'object'])) {
            throw new \InvalidArgumentException(
                sprintf('Invalid builder type "%s. Needs to be one of %s.', $type, implode(', ', ['document', 'asset', 'object']))
            );
        }

        if (!in_array(DataBuilderInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), DataBuilderInterface::class, implode(', ', class_implements($service)))
            );
        }

        if (!isset($this->builder[$type])) {
            $this->builder[$type] = [];
        }

        $this->builder[$type][$identifier] = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function getByTypeAndIdentifier(string $type, string $identifier)
    {
        return $this->builder[$type][$identifier];
    }
}