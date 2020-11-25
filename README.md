# Dynamic Search | Data Provider: Trinity Data

[![Software License](https://img.shields.io/badge/license-GPLv3-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Latest Release](https://img.shields.io/packagist/v/dachcom-digital/dynamic-search-data-provider-trinity.svg?style=flat-square)](https://packagist.org/packages/dachcom-digital/dynamic-search-data-provider-trinity)
[![Travis](https://img.shields.io/travis/com/dachcom-digital/pimcore-dynamic-search-data-provider-trinity/master.svg?style=flat-square)](https://travis-ci.com/dachcom-digital/pimcore-dynamic-search-data-provider-trinity)
[![PhpStan](https://img.shields.io/badge/PHPStan-level%202-brightgreen.svg?style=flat-square)](#)

A Data Fetch Extension for [Pimcore Dynamic Search](https://github.com/dachcom-digital/pimcore-dynamic-search). Crawl Pimcore Data `assets`, `documents` and `objects`.

## Requirements
- Pimcore >= 6.3
- Symfony >= 4.4
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

| Name                               | Default Value                         | Description |
|:-----------------------------------|:--------------------------------------|:------------|
| `index_asset`                      | false                                 |             |
| `asset_data_builder_identifier`    | true                                  |             |
| `asset_types`                      | `Asset::$types[]`, except folder      |             |
| `asset_additional_params`          | []                                    |             |
|                                    |                                       |             |
| `index_object`                     | false                                 |             |
| `object_ignore_unpublished`        | true                                  |             |
| `object_data_builder_identifier`   | 'default'                             |             |
| `object_types`                     | `DataObject::$types[]`, except folder |             |
| `object_class_names`               | []                                    |             |
| `object_additional_params`         | []                                    |             |
|                                    |                                       |             |
| `index_document`                   | false                                 |             |
| `document_ignore_unpublished`      | true                                  |             |
| `document_data_builder_identifier` | 'default'                             |             |
| `document_types`                   | `Document::$types`, except folder     |             |
| `document_additional_params`       | []                                    |             |

### full_dispatch

| Name                                 | Default Value | Description |
|:-------------------------------------|:--------------|:------------|
|`asset_limit`                         | 0             |             |
|`object_limit`                        | 0             |             |
|`document_limit`                      | 0             |             |

***

## Resource Normalizer

### trinity_default_resource_normalizer
Scaffold simple documents
Options: none

### trinity_localized_resource_normalizer
Scaffold localized documents

Options:

| Name                          | Default Value                           | Allowed Type    | Description |
|:------------------------------|:----------------------------------------|:----------------|:------------|
|`locales`                      | all pimcore enabled languages           | array           |             |
|`skip_not_localized_documents` | true                                    | bool            | if false, an exception rises if a document/object has no valid locale |

***

## Transformer

### Scaffolder

##### TrinityDataScaffolder
Identifier: `trinity_data_scaffolder`   
Simple object scaffolder.   
Supported types: `Asset`, `Document`, `DataObject\Concrete`.

### Field Transformer

##### ElementIdExtractor
Identifier: `element_id_extractor`   
Returns id of element   

Return Type: `string|int|null`   
Options: none   

##### ElementPropertyExtractor
Identifier: `element_property_extractor`   
Returns element property.   

Return Type: `string|null`   
Options:   

| Name                         | Default Value | Allowed Type   | Description |
|:-----------------------------|:--------------|:---------------|:------------|
|`property`                    | null          | string         |             |
|`object_getter`               | null          | null|string    |             |
|`allow_inherited_value`       | true          | bool           |             |


##### NormalizerValueCallback
Identifier: `normalizer_value_callback`   
Returns given option `value`.   

Return Type: `string|null`   
Options:   

| Name                         | Default Value | Allowed Type   | Description |
|:-----------------------------|:--------------|:---------------|:------------|
|`value`                       | null          | string         |             |

##### ObjectGetterExtractor
Identifier: `object_getter_extractor`   
Returns value of object getter.   

Return Type: `string|null`   
Options:   

| Name                         | Default Value | Allowed Type   | Description |
|:-----------------------------|:--------------|:---------------|:------------|
|`method`                      | id            | string         |             |
|`arguments`                   | []            | array          |             |

##### ObjectRelationsGetterExtractor
Identifier: `object_relations_getter_extractor`   
Returns values of object relations getter.   

Return Type: `array|null`   
Options:   

| Name                         | Default Value | Allowed Type   | Description |
|:-----------------------------|:--------------|:---------------|:------------|
|`relations`                   | null          | string         |             |
|`method`                      | id            | string         |             |


##### ObjectPathGenerator
Identifier: `object_path_generator`   
Returns object path generated by link generator.   

Return Type: `string|null`   
Options:   

| Name                         | Default Value | Allowed Type   | Description |
|:-----------------------------|:--------------|:---------------|:------------|
|`arguments`                   | []            | array          |             |
|`fetch_object_for_variant`    | false         | bool           | If true and object is type of `variant` the next parent gets fetched first until type `object` has been reached |

##### DocumentMetaExtractor
Identifier: `document_meta_extractor`   
Returns documents meta title or description.   

Return Type: `string|null`   
Options:   

| Name                         | Default Value | Allowed Type   | Description |
|:-----------------------------|:--------------|:---------------|:------------|
|`type`                        | title         | string         | Possible Types: `title` or `description` |

##### DocumentPathGenerator
Identifier: `document_path_generator`   
Returns real full path of document   

Return Type: `string|null`   
Options: none   

##### PdfDataExtractor
Identifier: `asset_pdf_extractor`   
Extracts pdf content with ghostscript   

Return Type: `string|null`   
Options: none

##### AssetPathGenerator
Identifier: `asset_path_generator`   
Returns real full path of document   

Return Type: `string|null`   
Options: none   
