<?php

namespace DsTrinityDataBundle;

final class DsTrinityDataEvents
{
    /**
     * Use the PROXY_ELEMENT_OBJECT event to overrule the data object proxy
     * This event comes within the Event/DataProxyEvent class.
     */
    public const PROXY_DEFAULT_DATA_OBJECT = 'ds.trinity_data.proxy.default.data_object';

    /**
     * Use the LISTING_QUERY_OBJECTS event to modify objects listing
     * This event also provides all given query options, defined in data_provider.options.
     */
    public const LISTING_QUERY_OBJECTS = 'ds.trinity_data.query.objects';

    /**
     * Use the LISTING_QUERY_ASSETS event to modify assets listing
     * This event also provides all given query options, defined in data_provider.options.
     */
    public const LISTING_QUERY_ASSETS = 'ds.trinity_data.query.assets';

    /**
     * Use the LISTING_QUERY_DOCUMENTS event to modify documents listing
     * This event also provides all given query options, defined in data_provider.options.
     */
    public const LISTING_QUERY_DOCUMENTS = 'ds.trinity_data.query.documents';
}
