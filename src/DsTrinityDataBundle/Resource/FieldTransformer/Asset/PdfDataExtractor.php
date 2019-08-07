<?php

namespace DsTrinityDataBundle\Resource\FieldTransformer\Asset;

use DynamicSearchBundle\Resource\Container\ResourceContainerInterface;
use DynamicSearchBundle\Resource\FieldTransformerInterface;
use Pimcore\Document\Adapter\Ghostscript;
use Pimcore\Model\Asset\Document;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PdfDataExtractor implements FieldTransformerInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['type']);
        $resolver->setAllowedTypes('type', ['string']);
        $resolver->setAllowedValues('type', ['description', 'title']);
        $resolver->setDefaults([
            'type' => 'title'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function transformData(string $dispatchTransformerName, ResourceContainerInterface $resourceContainer)
    {
        if (!$resourceContainer->hasAttribute('type')) {
            return null;
        }

        $data = $resourceContainer->getResource();
        if (!$data instanceof Document) {
            return null;
        }

        $data = $this->extractPdfData($data);

        return $data;
    }

    /**
     * @param Document $data
     *
     * @return string|null
     */
    protected function extractPdfData(Document $data)
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
            $cmd = sprintf('%s "%s" "%s"', $verboseCommand, $data->getFileSystemPath(), $tmpFile);
            exec($pdfToTextBin . ' ' . $cmd);
        } catch (\Exception $e) {
            return null;
        }

        $pdfContent = null;
        if (is_file($tmpFile)) {
            $fileContent = file_get_contents($tmpFile);
            $pdfContent = preg_replace("/\r|\n/", ' ', $fileContent);
            $pdfContent = preg_replace('/[^\p{Latin}\d ]/u', '', $pdfContent);
            $pdfContent = preg_replace('/\n[\s]*/', "\n", $pdfContent);

            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }

        return $pdfContent;
    }
}
