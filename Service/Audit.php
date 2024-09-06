<?php

namespace Crealoz\EasyAudit\Service;

use Crealoz\EasyAudit\Service\Type\TypeFactory;
use Magento\Framework\Exception\FileSystemException;
use Symfony\Component\Console\Output\OutputInterface;

class Audit
{

    protected array $results = [];

    public function __construct(
        protected readonly PDFWriter         $pdfWriter,
        protected readonly TypeFactory       $typeFactory,
        private readonly ArrayTools          $arrayTools,
        protected array                      $processors = []
    )
    {
        foreach ($this->processors as $sectionName => $processors) {
            dump($sectionName);
            foreach ($processors as $processorName => $processor) {
                dump($processorName);
                dump(array_keys($processor));
            }
        }
    }

    /**
     * @throws \Zend_Pdf_Exception
     * @throws FileSystemException
     */
    public function run(OutputInterface $output = null): string
    {
        foreach ($this->processors as $typeName => $subTypes) {
            $type = $this->typeFactory->create($typeName);
            $this->results[$typeName] = $type->process($subTypes, $typeName, $output);
        }
        if ($output instanceof OutputInterface) {
            $output->writeln(PHP_EOL.'Creating PDF...');
        }
        return $this->pdfWriter->createdPDF($this->results);
    }

    public function getAvailableProcessors(): array
    {
        return $this->processors;
    }

    /**
     * Maps the requested processors to the available ones
     * @param array $processors
     * @return void
     */
    public function setProcessors(array $processors): void
    {
        $availableProcessors = $this->getAvailableProcessors();
        $requiredProcessors = $this->arrayTools->recursiveArrayIntersect($availableProcessors, $processors);
        $this->processors = $requiredProcessors;
    }
}