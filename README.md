# Dynamic Search | Data Provider: Trinity Data

[![Software License](https://img.shields.io/badge/license-GPLv3-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Software License](https://img.shields.io/badge/license-DCL-white.svg?style=flat-square&color=%23ff5c5c)](LICENSE.md)
[![Latest Release](https://img.shields.io/packagist/v/dachcom-digital/dynamic-search-data-provider-trinity.svg?style=flat-square)](https://packagist.org/packages/dachcom-digital/dynamic-search-data-provider-trinity)
[![Tests](https://img.shields.io/github/actions/workflow/status/dachcom-digital/pimcore-dynamic-search-data-provider-trinity/.github/workflows/codeception.yml?branch=master&style=flat-square&logo=github&label=codeception)](https://github.com/dachcom-digital/pimcore-dynamic-search-data-provider-trinity/actions?query=workflow%3ACodeception+branch%3Amaster)
[![PhpStan](https://img.shields.io/github/actions/workflow/status/dachcom-digital/pimcore-dynamic-search-data-provider-trinity/.github/workflows/php-stan.yml?branch=master&style=flat-square&logo=github&label=phpstan%20level%204)](https://github.com/dachcom-digital/pimcore-dynamic-search-data-provider-trinity/actions?query=workflow%3A"PHP+Stan"+branch%3Amaster)

A data fetch extension for [Pimcore Dynamic Search](https://github.com/dachcom-digital/pimcore-dynamic-search). 
Fetch pimcore elements by listings: `assets`, `documents` and `objects`.

## Release Plan
| Release | Supported Pimcore Versions | Supported Symfony Versions | Release Date | Maintained         | Branch                                                                                          |
|---------|----------------------------|----------------------------|--------------|--------------------|-------------------------------------------------------------------------------------------------|
| **3.x** | `11.0`                     | `^6.4`                     | 28.09.2023   | Feature Branch     | master                                                                                          |
| **2.x** | `10.0` - `10.6`            | `^5.4`                     | 19.12.2021   | No                 | [1.x](https://github.com/dachcom-digital/pimcore-dynamic-search-data-provider-trinity/tree/2.x) |
| **1.x** | `6.6` - `6.9`              | `^4.4`                     | 18.04.2021   | No                 | [1.x](https://github.com/dachcom-digital/pimcore-dynamic-search-data-provider-trinity/tree/1.x) |

***

## Installation  
```json
"require" : {
    "dachcom-digital/dynamic-search" : "~3.0.0",
    "dachcom-digital/dynamic-search-data-provider-trinity" : "~3.0.0"
}
```

### Dynamic Search Bundle
You need to install / enable the Dynamic Search Bundle first.
Read more about it [here](https://github.com/dachcom-digital/pimcore-dynamic-search#installation).
After that, proceed as followed:

Add Bundle to `bundles.php`:
```php
<?php

return [
    \DsTrinityDataBundle\DsTrinityDataBundle::class => ['all' => true],
];
```

***

## Publishing State
**This Bundle will fetch unpublished elements by default**. This is crucial and also a problematic fact.
Read more about it [here](./docs/10_publishUnpublishedElements.md) to learn how to handle the publishing state of pimcore elements.

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

| Name                               | Default Value                           | Description |
|:-----------------------------------|:----------------------------------------|:------------|
| `index_asset`                      | false                                   |             |
| `asset_data_builder_identifier`    | true                                    |             |
| `asset_types`                      | `Asset::getTypes()`, except folder      |             |
| `asset_additional_params`          | []                                      |             |
|                                    |                                         |             |
| `index_object`                     | false                                   |             |
| `object_data_builder_identifier`   | 'default'                               |             |
| `object_types`                     | `DataObject::getTypes()`, except folder |             |
| `object_class_names`               | []                                      |             |
| `object_additional_params`         | []                                      |             |
|                                    |                                         |             |
| `index_document`                   | false                                   |             |
| `document_data_builder_identifier` | 'default'                               |             |
| `document_types`                   | `Document::getTypes()`, except folder   |             |
| `document_additional_params`       | []                                      |             |

### full_dispatch

| Name             | Default Value | Description |
|:-----------------|:--------------|:------------|
| `asset_limit`    | 0             |             |
| `object_limit`   | 0             |             |
| `document_limit` | 0             |             |

***

## Resource Normalizer

### trinity_default_resource_normalizer
Scaffold simple documents
Options: none

### trinity_localized_resource_normalizer
Scaffold localized documents

Options:

| Name                           | Default Value                 | Allowed Type | Description                                                           |
|:-------------------------------|:------------------------------|:-------------|:----------------------------------------------------------------------|
| `locales`                      | all pimcore enabled languages | array        |                                                                       |
| `skip_not_localized_documents` | true                          | bool         | if false, an exception rises if a document/object has no valid locale |

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

| Name                    | Default Value | Allowed Type | Description |
|:------------------------|:--------------|:-------------|:------------|
| `property`              | null          | string       |             |
| `object_getter`         | null          | null         | string      |
| `allow_inherited_value` | true          | bool         |             |


##### NormalizerValueCallback
Identifier: `normalizer_value_callback`   
Returns given option `value`.   

Return Type: `string|null`   
Options:   

| Name    | Default Value | Allowed Type | Description |
|:--------|:--------------|:-------------|:------------|
| `value` | null          | string       |             |

##### ObjectGetterExtractor
Identifier: `object_getter_extractor`   
Returns value of object getter.   

Return Type: `bool|int|float|string|array|null`   
Options:   

| Name                 | Default Value | Allowed Type  | Description                                     |
|:---------------------|:--------------|:--------------|:------------------------------------------------|
| `method`             | id            | string        |                                                 |
| `arguments`          | []            | array         |                                                 |
| `clean_string`       | true          | bool          | cleans HTML and whitespace (if value is string) |
| `transform_callback` | null          | null\|closure | a callback function for further value transform |

##### ObjectRelationsGetterExtractor
Identifier: `object_relations_getter_extractor`   
Returns values of object relations getter.   

Return Type: `array|null`   
Options:   

| Name        | Default Value | Allowed Type | Description |
|:------------|:--------------|:-------------|:------------|
| `relations` | null          | string       |             |
| `method`    | id            | string       |             |
| `arguments` | []            | array        |             |


##### ObjectPathGenerator
Identifier: `object_path_generator`   
Returns object path generated by link generator.   

Return Type: `string|null`   
Options:   

| Name                       | Default Value | Allowed Type | Description                                                                                                     |
|:---------------------------|:--------------|:-------------|:----------------------------------------------------------------------------------------------------------------|
| `arguments`                | []            | array        |                                                                                                                 |
| `fetch_object_for_variant` | false         | bool         | If true and object is type of `variant` the next parent gets fetched first until type `object` has been reached |

##### DocumentMetaExtractor
Identifier: `document_meta_extractor`   
Returns documents meta title or description.   

Return Type: `string|null`   
Options:   

| Name   | Default Value | Allowed Type | Description                              |
|:-------|:--------------|:-------------|:-----------------------------------------|
| `type` | title         | string       | Possible Types: `title` or `description` |

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

***

## Upgrade Info
Before updating, please [check our upgrade notes!](./UPGRADE.md)  

## License
**DACHCOM.DIGITAL AG**, Löwenhofstrasse 15, 9424 Rheineck, Schweiz  
[dachcom.com](https://www.dachcom.com), dcdi@dachcom.ch  
Copyright © 2025 DACHCOM.DIGITAL. All rights reserved.  

For licensing details please visit [LICENSE.md](LICENSE.md)  
