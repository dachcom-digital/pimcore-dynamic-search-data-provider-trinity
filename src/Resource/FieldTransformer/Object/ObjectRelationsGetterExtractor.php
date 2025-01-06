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

use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use DynamicSearchBundle\Resource\FieldTransformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectRelationsGetterExtractor implements FieldTransformerInterface
{
    protected array $options;

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['relations', 'arguments', 'method']);
        $resolver->setAllowedTypes('relations', ['string']);
        $resolver->setAllowedTypes('arguments', ['array']);
        $resolver->setAllowedTypes('method', ['string']);
        $resolver->setDefaults([
            'method'    => 'id',
            'arguments' => []
        ]);
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function transformData(string $dispatchTransformerName, ResourceContainerInterface $resourceContainer): ?array
    {
        $data = $resourceContainer->getResource();

        $relationsGetter = sprintf('get%s', ucfirst($this->options['relations']));

        if (!method_exists($data, $relationsGetter)) {
            return null;
        }

        $relations = call_user_func([$data, $relationsGetter]);
        if (!is_array($relations)) {
            return null;
        }

        $values = [];
        foreach ($relations as $relation) {
            if (!method_exists($relation, $this->options['method'])) {
                return null;
            }

            $values[] = call_user_func_array([$relation, $this->options['method']], $this->options['arguments']);
        }

        return $values;
    }
}
