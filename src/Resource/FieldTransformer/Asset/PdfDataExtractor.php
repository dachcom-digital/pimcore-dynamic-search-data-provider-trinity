<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace DsTrinityDataBundle\Resource\FieldTransformer\Asset;

use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use DynamicSearchBundle\Resource\FieldTransformerInterface;
use Pimcore\Document\Adapter\Ghostscript;
use Pimcore\Model\Asset\Document;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PdfDataExtractor implements FieldTransformerInterface
{
    protected array $options;

    public function configureOptions(OptionsResolver $resolver): void
    {
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function transformData(string $dispatchTransformerName, ResourceContainerInterface $resourceContainer): ?string
    {
        if (!$resourceContainer->hasAttribute('type')) {
            return null;
        }

        $data = $resourceContainer->getResource();
        if (!$data instanceof Document) {
            return null;
        }

        return $this->extractPdfData($data);
    }

    protected function extractPdfData(Document $data): ?string
    {
        $assetTmpDir = PIMCORE_SYSTEM_TEMP_DIRECTORY;

        try {
            $pdfToTextBin = Ghostscript::getPdftotextCli();
        } catch (\Exception $e) {
            $pdfToTextBin = false;
        }

        if ($pdfToTextBin === false) {
            return null;
        }

        $textFileTmp = uniqid('t2p-');

        $tmpFile = $assetTmpDir . DIRECTORY_SEPARATOR . $textFileTmp . '.txt';

        $verboseCommand = \Pimcore::inDebugMode() ? '-q' : '';

        try {
            $cmd = sprintf('%s "%s" "%s"', $verboseCommand, $data->getLocalFile(), $tmpFile);
            exec($pdfToTextBin . ' ' . $cmd);
        } catch (\Exception $e) {
            return null;
        }

        $pdfContent = null;
        if (is_file($tmpFile)) {
            $fileContent = file_get_contents($tmpFile);
            $pdfContent = preg_replace("/\r|\n/", ' ', $fileContent);
            $pdfContent = preg_replace('/[^\p{L}\d ]/u', '', $pdfContent);
            $pdfContent = preg_replace('/\n[\s]*/', "\n", $pdfContent);

            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }

        return $pdfContent;
    }
}
