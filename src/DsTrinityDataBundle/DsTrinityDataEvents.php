<?php

namespace DsTrinityDataBundle;

final class DsTrinityDataEvents
{
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
