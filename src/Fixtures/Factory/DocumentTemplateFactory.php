<?php

namespace PTS\SyliusOrderBatchPlugin\Fixtures\Factory;

use PTS\SyliusOrderBatchPlugin\Entity\DocumentTemplate;
use PTS\SyliusOrderBatchPlugin\Service\DocumentsDataProvider;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Factory\Factory;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class DocumentTemplateFactory
{
    /**
     * @var FactoryInterface
     */
    private $documentTemplateFactory;

    /**
     * @var RepositoryInterface
     */
    private $localeRepository;

    /**
     * @var DocumentsDataProvider
     */
    private $documentsDataProvider;


    private $projectDir;

    /**
     * DocumentTemplateFactory constructor.
     * @param RepositoryInterface $localeRepository
     * @param DocumentsDataProvider $documentsDataProvider
     * @param string $projectDir
     */
    public function __construct(
        RepositoryInterface $localeRepository,
        DocumentsDataProvider $documentsDataProvider,
        string $projectDir
    ) {
        $this->documentTemplateFactory = new Factory(DocumentTemplate::class);
        $this->localeRepository = $localeRepository;
        $this->documentsDataProvider = $documentsDataProvider;
        $this->projectDir = $projectDir;
    }

    public function create(array $options = [])
    {
        $locale = $this->localeRepository->findOneBy(['code' => $options['locale']]);

        /** @var DocumentTemplate $documentTemplate */
        $documentTemplate = $this->documentTemplateFactory->createNew();
        $document = [];

        $documentData = $this->documentsDataProvider->getTemplate($document, $this->projectDir);
        $documentTemplate->setCode($options['code']);
        $documentTemplate->setTitle($options['title']);
        $documentTemplate->setLocale($locale);
        $documentTemplate->setStyle($documentData['style']);
        $documentTemplate->setContent($documentData['content']);
        $documentTemplate->setTemplateData($documentData['data']);

        return $documentTemplate;
    }
}
