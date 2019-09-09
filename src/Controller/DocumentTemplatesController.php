<?php

namespace PTS\SyliusOrderBatchPlugin\Controller;

use PTS\SyliusOrderBatchPlugin\Entity\DocumentTemplate;
use Sylius\Component\Core\Model\Order;
use PTS\SyliusOrderBatchPlugin\Form\Type\DocumentTemplateType;
use PTS\SyliusOrderBatchPlugin\Service\DocumentsDataProvider;
use PTS\SyliusOrderBatchPlugin\Service\DocumentsManager;
use PTS\SyliusOrderBatchPlugin\Service\FormErrorExtractor;
use Sylius\Component\Locale\Model\Locale;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class DocumentTemplatesController extends Controller
{
    public function documentTemplateListAction()
    {
        $documents = $this->getParameter('emailDocuments');

        /** @var DocumentsManager $documentManager */
        $documentManager = $this->get('pts_sylius_order_batch_plugin.document.manager');
        $em = $this->getDoctrine()->getManager();

        $localeRepo = $this->getDoctrine()->getRepository(Locale::class);
        $locales = $localeRepo->findAll();

        $documentTemplatesRepo = $this->getDoctrine()->getRepository(DocumentTemplate::class);
        $documentTemplates = $documentTemplatesRepo->findAll();

        $newTemplates = false;

        $projectPath = $this->getParameter('kernel.project_dir');

        /** @var DocumentsDataProvider $documentDataProvider */
        $documentDataProvider = $this->get('app.document_data.provider');

        foreach ($documents as $document) {
            /** @var Locale $locale */
            foreach ($locales as $locale) {
                $exists = false;

                if (sizeof($documentTemplates) == 0) {
                    $exists = false;
                    $newTemplates = true;
                } else {
                    /** @var DocumentTemplate $template */
                    foreach ($documentTemplates as $template) {
                        if ($template->getLocale()->getId() == $locale->getId()
                            && $template->getCode() == $document['code']) {
                            $exists = true;
                        } else {
                            $newTemplates = true;
                        }
                    }
                }

                if ($exists == false) {
                    $templateData = $documentDataProvider->getTemplate($document, $projectPath);
                    $newTemplate = $documentManager->generateDocumentTemplate($document, $locale, $templateData);

                    $em->persist($newTemplate);
                }
            }
        }

        if ($newTemplates) {
            $em->flush();
        }

        $localeCode = $this->getParameter('locale');
        $locale = $localeRepo->findOneBy(['code' => $localeCode]);

        return $this->render('Admin/DocumentTemplates/DocumentTemplatesList.html.twig', [
            'documents' => $documents,
            'localeId' => $locale->getId()
        ]);
    }

    public function documentTemplateEditAction(Request $request)
    {
        $localeCode = $request->attributes->get('localeId');
        $dm = $this->getDoctrine();
        $session = $this->get('session');
        $translator = $this->get('translator');

        $localeRepo = $dm->getRepository(Locale::class);
        if ($localeCode == '') {
            $localeCode = $this->getParameter('locale');
            /** @var Locale $locale */
            $locale = $localeRepo->findOneBy(['code' => $localeCode]);
        } else {
            $locale = $localeRepo->find($localeCode);

            if (is_null($locale)) {
                $localeCode = $this->getParameter('locale');
                $locale = $localeRepo->findOneBy(['code' => $localeCode]);
            }
        }
        $locales = $localeRepo->findAll();
        $documents = $this->getParameter('emailDocuments');
        $documentName = $request->attributes->get('code');

        $doc = null;

        foreach ($documents as $item ) {
            if ($item['code'] == $documentName) {
                $doc = $item;
            }
        }

        if (is_null($doc)) {
            $session->getFlashBag()->add(
                'error',
                $translator->trans('app.editDocumentTemplates.messages.error.notExists')

            );

            return new RedirectResponse($this->generateUrl('app_admin_document_template_list'));
        }

        /** @var DocumentTemplate $documentTemplate */
        $documentTemplate = $this->getDocumentTemplateByCodeAndLocale($doc['code'], $locale->getCode());

        $form = $this->createForm(DocumentTemplateType::class, $documentTemplate, [
            'csrf_protection' => false
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $dm->getManager()->flush();
                $session->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('app.messages.success.saved')
                );
            } else {
                /** @var FormErrorExtractor $formErrorExtractor */
                $formErrorExtractor = $this->get('app.form.error.extractor');
                $errors = $formErrorExtractor->getErrorMessages($form);

                foreach ($errors as $error) {
                    foreach ($error as $item) {
                        $session->getFlashBag()->add('error', $item);
                    }
                }
            }
        }

        $templateData = $documentTemplate->getTemplateData();

        if (is_null($templateData)) {
            $templateData = $doc['exampleData'];
            $session->getFlashBag()->add(
                'error',
                $translator->trans('app.editDocumentTemplates.messages.error.dataCorrupted')
            );
        }

        return $this->render('Admin/DocumentTemplates/DocumentEdit.html.twig', [
            'locale' => $locale,
            'locales' => $locales,
            'document' => $doc,
            'template' => $documentTemplate,
            'documentTemplateData' => $templateData,
            'form' => $form->createView(),
        ]);
    }

    public function documentPreviewAction(Request $request) {

        $localeCode = $request->attributes->get('localeId');

        /** @var DocumentsManager $documentsManager */
        $documentsManager = $this->get('pts_sylius_order_batch_plugin.document.manager');

        /** @var DocumentsDataProvider $documentDataProvider */
        $documentDataProvider = $this->get('app.document_data.provider');

        /** @var Order $order */
        $order = $documentDataProvider->getTestOrder();

        $code = $request->attributes->get('code');

        $documentTemplate = $this->getDocumentTemplateByCodeAndLocale($code, $localeCode);

        $data = [
            'title' => $documentTemplate->getTitle(),
            'templateData' => $documentTemplate->getTemplateData()
        ];

        if (is_null($data)) {
            $documents = $this->getParameter('emailDocuments');
            foreach ($documents as $document) {
                if ($document['code'] == $code) {
                    $data = [
                        'title' => $document['title'],
                        'templateData' => $document['exampleData']
                    ];
                }
            }
        }

        return $documentsManager->createInvoiceStream($order, $code, $data);
    }

    public function regenerateAction($code)
    {
        $session = $this->get('session');
        $documentTemplateRepo = $this->getDoctrine()->getRepository(DocumentTemplate::class);
        $documents = $documentTemplateRepo->findBy(['code' => $code]);
        /** @var DocumentTemplate $document */
        foreach ($documents as $document) {
            $documentName = $document->getTitle();
            $this->getDoctrine()->getManager()->remove($document);
        }
        $documentName = $documentName ?? 'Template';
        $this->getDoctrine()->getManager()->flush();
        $session->getFlashBag()->add(
            'success',
            $documentName . $this->get('translator')->trans('app.messages.success.regenerated')
        );
        return $this->redirectToRoute('app_admin_document_template_list');
    }

    private function getDocumentTemplateByCodeAndLocale($code, $localeCode)
    {
        $dm = $this->getDoctrine();
        $localeRepo = $dm->getRepository(Locale::class);
        if ($localeCode == '') {
            $localeCode = $this->getParameter('locale');
            $locale = $localeRepo->findOneBy(['code' => $localeCode]);
        } else {
            $locale = $localeRepo->find($localeCode);

            if (is_null($locale)) {
                $localeCode = $this->getParameter('locale');
                $locale = $localeRepo->findOneBy(['code' => $localeCode]);
            }
        }

        $session = $this->get('session');
        $translator = $this->get('translator');

        $documents = $this->getParameter('emailDocuments');

        $doc = null;

        foreach ($documents as $item ) {
            if ($item['code'] == $code) {
                $doc = $item;
            }
        }

        if (is_null($doc)) {
            $session->getFlashBag()->add(
                'error',
                $translator->trans('app.editDocumentTemplates.messages.error.notExists')

            );

            return new RedirectResponse($this->generateUrl('app_admin_document_template_list'));
        }

        $documentTemplateRepo = $dm->getRepository(DocumentTemplate::class);
        /** @var DocumentTemplate $documentTemplate */
        $documentTemplate = $documentTemplateRepo->findOneBy(['code' => $code, 'locale' => $locale->getId()]);
        return $documentTemplate;
    }
}
