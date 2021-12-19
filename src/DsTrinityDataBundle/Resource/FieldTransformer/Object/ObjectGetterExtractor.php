<?php

namespace DsTrinityDataBundle\Resource\FieldTransformer\Object;

use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use DynamicSearchBundle\Resource\FieldTransformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectGetterExtractor implements FieldTransformerInterface
{
    protected array $options;

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['method', 'arguments', 'clean_string']);
        $resolver->setAllowedTypes('method', ['string']);
        $resolver->setAllowedTypes('arguments', ['array']);
        $resolver->setAllowedTypes('clean_string', ['boolean']);
        $resolver->setDefaults([
            'method'       => 'id',
            'clean_string' => true,
            'arguments'    => []
        ]);
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function transformData(string $dispatchTransformerName, ResourceContainerInterface $resourceContainer): ?string
    {
        if (!$resourceContainer->hasAttribute('type')) {
            return null;
        }

        $data = $resourceContainer->getResource();
        if (!method_exists($data, $this->options['method'])) {
            return null;
        }

        $value = call_user_func_array([$data, $this->options['method']], $this->options['arguments']);
        if (!is_string($value)) {
            return null;
        }

        if ($this->options['clean_string'] === true) {
            return trim(preg_replace('/\s+/', ' ', strip_tags($value)));
        }

        return $value;
    }
}
