<?php

namespace PTS\SyliusOrderBatchPlugin\Service;

use PTS\SyliusOrderBatchPlugin\Entity\DocumentTemplate;
use Sylius\Component\Core\Model\Order;
use PTS\SyliusOrderBatchPlugin\Entity\Document;
use PlumTreeSystems\FileBundle\Service\GaufretteFileManager;
use PTS\SyliusOrderBatchPlugin\Repository\DocumentRepository;
use PTS\SyliusOrderBatchPlugin\Repository\DocumentTemplateRepository;
use Doctrine\ORM\EntityManager;
use Dompdf\Dompdf;
use Dompdf\Options;
use JMS\Serializer\Serializer;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentsManager
{

    /** @var EntityManager $em */
    private $em;

    /**
     * @var \Twig_Environment
     */
    private $rendererAdapter;

    private $fileSystemSettings;

    /**
     * @var GaufretteFileManager
     */
    private $fileManager;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * DocumentsManager constructor.
     * @param EntityManager $em
     * @param \Twig_Environment $rendererAdapter
     * @param $fileSystemSettings
     * @param GaufretteFileManager $fileManager
     * @param Serializer $serializer
     */
    public function __construct(
        EntityManager $em,
        \Twig_Environment $rendererAdapter,
        $fileSystemSettings,
        GaufretteFileManager $fileManager,
        Serializer $serializer
    )
    {
        $this->em = $em;
        $this->rendererAdapter = $rendererAdapter;
        $this->fileSystemSettings = $fileSystemSettings;
        $this->fileManager = $fileManager;
    }

    public function streamDocument(Pagerfanta $iterator)
    {
        /** @var DocumentTemplateRepository $documentTemplateRepo */
        $documentTemplateRepo = $this->em->getRepository(DocumentTemplate::class);
        $documentTemplate = $documentTemplateRepo->getDocumentTemplate('shippingNote', 'en_US');

        if (is_null($documentTemplate)) {
            return;
        }

        $parts = $this->createFileStructureSplit($documentTemplate);
        $bodyTemplate = $this->rendererAdapter->createTemplate($parts['body']);
        $tail = $this->rendererAdapter->render('Documents/shippingNoteBlocks/_tail.html.twig');

        $markPaginator = clone $iterator;
        $response = new StreamedResponse();
        $response->setCallback(
            function () use ($parts, $markPaginator, $bodyTemplate, $tail) {
                $markPaginator->setMaxPerPage(100);
                $next = true;
                echo $parts['head'];
                while($next) {
                    $results = $markPaginator->getCurrentPageResults();

                    /**
                     * @var Order $item
                     */
                    foreach ($results as $item) {
                        echo $bodyTemplate->render($this->generateDataForView('shippingNote', ['order' => $item]));
                        flush();
                    }

                    if ($markPaginator->hasNextPage()) {
                        $markPaginator->setCurrentPage($markPaginator->getNextPage());
                    } else {
                        $next = false;
                    }
                }

                echo $tail.$parts['tail'];
                flush();
            }
        );
        return $response;
    }

    public function createShippingNotesForArray(Pagerfanta $iterator)
    {
        /** @var DocumentTemplateRepository $documentTemplateRepo */
        $documentTemplateRepo = $this->em->getRepository(DocumentTemplate::class);
        $documentTemplate = $documentTemplateRepo->getDocumentTemplate('shippingNote', 'en_GB');

        if (is_null($documentTemplate)) {
            return;
        }

        $html = $this->createHtmlForMultiple($iterator, $documentTemplate);

        $pdf = $this->createPdfForMultiple($html);

        return $this->createStreamFromPdf($pdf, ['title' => 'Shipping_Note']);
    }

    /**
     * Creates Html from Pagerfanta iterator for multiple bodies
     * @param Pagerfanta $iterator
     * @param $template
     * @return string
     * @throws \Throwable
     */
    private function createHtmlForMultiple(Pagerfanta $iterator, $template)
    {
        $html = '';

        $parts = $this->createFileStructureSplit($template);

        $html .= $parts['head'];

        $bodyTemplate = $this->rendererAdapter->createTemplate($parts['body']);

        /**
         * @var Pagerfanta $markPaginator
         */
        $markPaginator = clone $iterator;
        $markPaginator->setMaxPerPage(100);

        $next = true;

        while($next) {
            $results = $markPaginator->getCurrentPageResults();

            /**
             * @var Order $item
             */
            foreach ($results as $item) {
                $viewData = $this->generateDataForView('shippingNote', ['order' => $item]);
                $interimHtml = $bodyTemplate->render($viewData);
                $html .= $interimHtml;
            }

            if ($markPaginator->hasNextPage()) {
                $markPaginator->setCurrentPage($markPaginator->getNextPage());
            } else {
                $next = false;
            }
        }

        $html .= $parts['tail'];
        return $html;
    }

    /**
     * Creates Pdf from html
     * Differs from other that this does not contain template logic
     * @param $html
     * @return Dompdf
     */
    private function createPdfForMultiple($html)
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($pdfOptions);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        return $dompdf;
    }

    public function createInvoice(Order $order,string $type)
    {

        /** @var DocumentTemplateRepository $documentTemplateRepo */
        $documentTemplateRepo = $this->em->getRepository(DocumentTemplate::class);
        $documentTemplate = $documentTemplateRepo->getDocumentTemplate($type, $order->getLocaleCode());

        if (is_null($documentTemplate)) {
            return;
        }

        $documentStructure = $this->createFileStructure($documentTemplate);

        $this->createDocument($documentStructure, $order, $type, ['title' => 'invoice']);
    }

    public function createInvoiceStream(Order $order,string $type, $data = [])
    {

        /** @var DocumentTemplateRepository $documentTemplateRepo */
        $documentTemplateRepo = $this->em->getRepository(DocumentTemplate::class);
        $documentTemplate = $documentTemplateRepo->getDocumentTemplate($type, $order->getLocaleCode());

        if (is_null($documentTemplate)) {
            return null;
        }

        $documentStructure = $this->createFileStructure($documentTemplate, $data);

        if ($type === 'shippingNote') {
            return $this->createHtmlView($order, $documentStructure, $type);
        }
        $this->createDocumentStream($order, $documentStructure, $type, $data);
    }

    private function createHtmlView($order, $documentStructure, $type)
    {
        $documentContent = $this->rendererAdapter->createTemplate($documentStructure);

        $viewData = $this->generateDataForView($type, ['order' => $order]);

        return new Response($documentContent->render($viewData));
    }

    private function createFileStructure(DocumentTemplate $template, $data = []) {

        $html = "<html><head><style>";

        if (!is_null($template->getStyle())) {
            $html = $html . $template->getStyle();
        }

        $html = $html . "</style></head><body>";

        if (!is_null($template->getContent())) {
            $html = $html . $template->getContent();
        }

        $html = $html . "</body></html>";

        if (
            array_key_exists('templateData', $data)
            && sizeof($data['templateData']) > 0) {
            $templateData = $data['templateData'];
        } else {
            $templateData = json_decode($template->getTemplateData(), true);
        }
        $htmlWithFields = $this->renderFieldsToTemplate($html, $templateData);
        return $htmlWithFields;
    }

    private function createFileStructureSplit(DocumentTemplate $template, $data = [])
    {
        $head = "<html><head><style>";

        if (!is_null($template->getStyle())) {
            $head .= $template->getStyle();
        }

        $head .= "</style></head>";

        $body = "<body>";

        if (!is_null($template->getContent())) {
            $body .= $template->getContent();
        }

        $body .= "</body>";

        $tail = "</html>";

        if (
            array_key_exists('templateData', $data)
            && sizeof($data['templateData']) > 0) {
            $templateData = $data['templateData'];
        } else {
            $templateData = json_decode($template->getTemplateData(), true);
        }

        $bodyWithFields = $this->renderFieldsToTemplate($body, $templateData);

        return [
            'head' => $head,
            'body' => $bodyWithFields,
            'tail' => $tail
        ];
    }

    private function createDocument(string $html, Order $order, $type, $data = [])
    {
        $dompdf = $this->createDomPdf($html, $order, $type);

        $document = $dompdf->output();

        $newFile = new Document();
        $publicDirectory = $this->fileSystemSettings['directory'];

        if (array_key_exists('title', $data)) {
            $title = $data['title'];
        } else {
            $title = 'document';
        }

        $filename = $title .'-' . $order->getNumber() .'.pdf';
        $hashName = md5(uniqid(mt_rand(), true));
        $pdfFilepath =  $publicDirectory . '/' . $hashName;

        file_put_contents($pdfFilepath, $document);
        $size = filesize($pdfFilepath);
        $newFile->addOrder($order);
        $newFile->setCode($type);

        $fileEntity = $this->fileManager->saveExistingFile($newFile, $filename, $hashName, 'application/pdf', $size, $publicDirectory);
        $this->em->persist($fileEntity);
        $this->em->flush();
    }

    private function createDocumentStream(Order $order, string $html, $type, $data)
    {
        $dompdf = $this->createDomPdf($html, $order, $type);

        if (array_key_exists('title', $data)) {
            $title = $data['title'];
        } else {
            $title = 'document';
        }
        // Output the generated PDF to Browser (inline view)
        return $dompdf->stream($title . ".pdf", [
            "Attachment" => false
        ]);
    }

    private function createStreamFromPdf($dompdf, $data)
    {
        if (array_key_exists('title', $data)) {
            $title = $data['title'];
        } else {
            $title = 'document';
        }
        // Output the generated PDF to Browser (inline view)
        return $dompdf->stream($title . ".pdf", [
            "Attachment" => false
        ]);
    }

    private function createDomPdf(string $html, Order $order, $type) {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($pdfOptions);

        $documentContent = $this->rendererAdapter->createTemplate($html);

        $viewData = $this->generateDataForView($type, ['order' => $order]);

        $templateHtml = $documentContent->render($viewData);

        $dompdf->loadHtml($templateHtml);
        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        return $dompdf;
    }


    private function generateDataForView($type, array $data) {
        switch ($type) {
            case 'orderInvoice':
            case 'shippingNote':
                return $this->orderInvoiceData($data);
        }
    }

    private function orderInvoiceData($data) {
        $order = $data['order'];
        $items = $order->getItems()->toArray();
        $images = [];
        $descriptions = [];

        foreach ($items as $item) {
            $imagesArray = $item->getProduct()->getImages()->toArray();
            if (sizeof($imagesArray) > 0) {
                $images[$item->getId()] = $imagesArray[0];
            }

            $descriptions[$item->getId()] = $item->getProduct();
        }

        return [
            'order' => $order,
            'images' => $images
        ];
    }

    private function renderFieldsToTemplate($html, $fieldsData) {
        if (!is_null($fieldsData)) {
            foreach ($fieldsData as $key => $item) {
                $html = str_replace('{{ ' . $key . ' }}', $item, $html);
            }
        }

        return $html;
    }

    public function generateDocumentTemplate($document, $locale, $templateData): DocumentTemplate {
        $newTemplate = new DocumentTemplate();
        $newTemplate->setTitle($document['title']);
        $newTemplate->setCode($document['code']);
        $newTemplate->setLocale($locale);
        $newTemplate->setStyle($templateData['style']);
        $newTemplate->setContent($templateData['content']);
        $newTemplate->setTemplateData($templateData['data']);

        return $newTemplate;
    }
}