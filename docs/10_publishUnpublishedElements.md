# The Publishing/Unpublishing Problem
Processing publish states of elements definitely sounds easier than it is.

## Prologue
In V1 of this bundle, there was a `object_ignore_unpublished` and `object_ignore_unpublished` option flag.
Back then, trinity skipped unpublished objects by default.

## New Ways
In V2 we're fetching unpublished elements by default. If we wouldn't allow unpublished elements,
they would stay forever in index. Imagine an published element which is published at index time.
But if you're going to unpublish it, and maybe you're editing this element afterwards, it wil never get updated again,
because the internal validator would disallow unpublished elements.

So what can you do? There are two options:

## Way I: Index every Element regardless its publishing state
You could fetch every document regardless its state. You could add a `published` flag to your document definition 
and filter by this flag in your frontend query.

## Way II: Resource Validation
Within the `DynamicSearchEvents::RESOURCE_CANDIDATE_VALIDATION` event, you're able to modify the state of resource.
So for example, you could disallow to fetch the unpublished element at `index` or `insert` operations but allow it to modify/delete it
on `update` or `delete` operations. Read more about the resource validator [here](https://github.com/dachcom-digital/pimcore-dynamic-search/blob/master/docs/40_ResourceValidator.md).
