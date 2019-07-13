<?php

namespace DsTrinityDataBundle\Service\Builder;

use Pimcore\Model\Document;

class DocumentListBuilder implements DataBuilderInterface
{
    /**
     * {@inheritDoc}
     */
    public function buildByList(array $options): array
    {
        $allowedTypes = $options['document_types'];
        $includeUnpublished = $options['document_ignore_unpublished'] === false;
        $limit = $options['document_limit'];
        $additionalParams = $options['document_additional_params'];

        $list = new Document\Listing();

        if ($includeUnpublished === true) {
            $list->setUnpublished(true);
        }

        foreach ($additionalParams as $additionalParam => $additionalValue) {
            $list->addConditionParam($additionalParam, $additionalValue);
        }

        if ($limit > 0) {
            $list->setLimit($limit);
        }

        $this->addDocumentTypeRestriction($list, $allowedTypes);

        return $list->getDocuments();
    }

    /**
     * {@inheritDoc}
     */
    public function buildById(int $id)
    {
        return Document::getById($id);
    }

    /**
     * @param Document\Listing $listing
     * @param array            $allowedTypes
     *
     * @return Document\Listing
     */
    protected function addDocumentTypeRestriction(Document\Listing $listing, array $allowedTypes)
    {
        if (count($allowedTypes) === 0) {
            return $listing;
        }

        $quotedTypes = [];
        foreach ($allowedTypes as $cName) {
            $quotedTypes[] = \Pimcore\Db::get()->quote($cName);
        }

        $listing->addConditionParam(sprintf('type in(%s)', implode(',', $quotedTypes)), '');

        return $listing;
    }
}