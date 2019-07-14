<?php

namespace DsTrinityDataBundle;

use DsTrinityDataBundle\DependencyInjection\Compiler\DataBuilderPass;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DsTrinityDataBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;

    const PACKAGE_NAME = 'dachcom-digital/dynamic-search-data-provider-trinity';

    const PROVIDER_NAME = 'trinity_data';

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DataBuilderPass());
    }

    /**
     * {@inheritdoc}
     */
    protected function getComposerPackageName(): string
    {
        return self::PACKAGE_NAME;
    }
}
