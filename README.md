# Dynamic Search | Data Provider: Trinity Data

[![Software License](https://img.shields.io/badge/license-GPLv3-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Latest Release](https://img.shields.io/packagist/v/dachcom-digital/dynamic-search-data-provider-trinity.svg?style=flat-square)](https://packagist.org/packages/dachcom-digital/dynamic-search-data-provider-trinity)
[![Travis](https://img.shields.io/travis/com/dachcom-digital/pimcore-dynamic-search-data-provider-trinity/master.svg?style=flat-square)](https://travis-ci.com/dachcom-digital/pimcore-dynamic-search-data-provider-trinity)
[![PhpStan](https://img.shields.io/badge/PHPStan-level%202-brightgreen.svg?style=flat-square)](#)

A Data Fetch Extension for [Pimcore Dynamic Search](https://github.com/dachcom-digital/pimcore-dynamic-search). Crawl Pimcore Data `assets`, `documents` and `objects`.

## Requirements
- Pimcore >= 5.8.0
- Pimcore Dynamic Search

***

## Basic Setup

```yaml
dynamic_search:
    context:
        default:
            data_provider:
                service: 'trinity_data'
                options:
                    always:
                        index_object: true
                        object_class_names:
                            - TestClass
                        index_document: true
                        index_asset: false
                    full_dispatch:
                        object_limit: 20
                        document_limit: 10
                normalizer:
                    service: 'trinity_localized_resource_normalizer'
```

***

## Provider Options

### always

| Name                                 | Default Value          | Description |
|:-------------------------------------|:-----------------------|:------------|
|`index_asset`                         | false                  |             |
|`asset_data_builder_identifier`       | true                   |             |
|`asset_types`                         | `Àsset::$types[]`      |             |
|`asset_additional_params`             | []                     |             |
|                                      |                        |             |
|`index_object`                        | false                  |             |
|`object_ignore_unpublished`           | true                   |             |
|`object_data_builder_identifier`      | 'default'              |             |
|`object_types`                        | `DataObject::$types[]` |             |
|`object_class_names`                  | []                     |             |
|`object_additional_params`            | []                     |             |
|                                      |                        |             |
|`index_document`                      | false                  |             |
|`document_ignore_unpublished`         | true                   |             |
|`document_data_builder_identifier`    | 'default'              |             |
|`document_types`                      | `Document::$types`     |             |
|`document_additional_params`          | []                     |             |

### full_dispatch

| Name                                 | Default Value | Description |
|:-------------------------------------|:--------------|:------------|
|`asset_limit`                         | 0             |             |
|`object_limit`                        | 0             |             |
|`document_limit`                      | 0             |             |
