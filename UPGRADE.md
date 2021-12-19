# Upgrade Notes

## Migrating from Version 1.x to Version 2.0.0

### Global Changes
- PHP8 return type declarations added: you may have to adjust your extensions accordingly
- `ProxyResolverInterface` (also the corresponding `ObjectProxyResolver` service) and tag `ds_trinity_data.proxy_resolver`has been removed, you have to use
  the [resource validator](https://github.com/dachcom-digital/pimcore-dynamic-search/blob/master/docs/40_ResourceValidator.md) now:
    - Method `checkResourceProxy` from `DataProviderService` has been removed
    - Option `object_proxy_identifier` and `object_proxy_settings` in provider configuration have been removed
    - Event `DataProxyEvent` via `DsTrinityDataEvents::PROXY_DEFAULT_DATA_OBJECT` has been removed
- The option `object_ignore_unpublished` and `object_ignore_unpublished` has [been removed](https://github.com/dachcom-digital/pimcore-dynamic-search-data-provider-trinity/issues/16)
- ⚠️ By default, unpublished elements will be fetched **by default**. Read more about this problematic fact [here](./docs/10_publishUnpublishedElements.md)

***

1.x Upgrade Notes: https://github.com/dachcom-digital/dynamic-search-data-provider-trinity/blob/1.x/UPGRADE.md