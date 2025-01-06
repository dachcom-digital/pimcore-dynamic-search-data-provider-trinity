<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace DsTrinityDataBundle\Resource\FieldTransformer\Object;

use DynamicSearchBundle\Exception\TransformerException;
use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use DynamicSearchBundle\Resource\FieldTransformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectGetterExtractor implements FieldTransformerInterface
{
    protected array $options;

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['method', 'arguments', 'clean_string', 'transform_callback']);
        $resolver->setAllowedTypes('method', ['string']);
        $resolver->setAllowedTypes('arguments', ['array']);
        $resolver->setAllowedTypes('clean_string', ['boolean']);
        $resolver->setAllowedTypes('transform_callback', ['null', 'closure']);
        $resolver->setDefaults([
            'method'                   => 'id',
            'clean_string'             => true,
            'arguments'                => [],
            'transform_callback'       => null,
        ]);
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function transformData(string $dispatchTransformerName, ResourceContainerInterface $resourceContainer): null|bool|int|float|string|array
    {
        if (!$resourceContainer->hasAttribute('type')) {
            return null;
        }

        $data = $resourceContainer->getResource();
        if (!method_exists($data, $this->options['method'])) {
            return null;
        }

        $value = call_user_func_array([$data, $this->options['method']], $this->options['arguments']);

        if (is_callable($this->options['transform_callback'])) {
            try {
                $value = $this->options['transform_callback']($value);
            } catch (\Throwable $e) {
                throw new TransformerException(
                    sprintf('error while executing transform_callback: %s', $e->getMessage())
                );
            }
        }

        if (is_string($value) && $this->options['clean_string'] === true) {
            return trim(preg_replace('/\s+/', ' ', strip_tags($value)));
        }

        return $value;
    }
}
