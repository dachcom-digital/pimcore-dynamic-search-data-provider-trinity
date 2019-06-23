<?php

namespace DsTrinityDataBundle\Service\Builder;

use Pimcore\Model\Document;

class DocumentListBuilder implements DataBuilderInterface
{
    public function build(array $options): array
    {
        $id = $options['id'];
        $allowedTypes = $options['document_types'];
        $includeUnpublished = $options['document_ignore_unpublished'] === false;

        $list = new Document\Listing();

        if ($includeUnpublished === true) {
            $list->setUnpublished(true);
        }

        if ($id !== null) {
            $list->addConditionParam('id = ?', $id);
        }

        $this->addDocumentTypeRestriction($list, $allowedTypes);

        return $list->getDocuments();
    }

    /**
     * @param Document\Listing $listing
     * @param array              $allowedTypes
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