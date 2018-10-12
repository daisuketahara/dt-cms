<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use App\Entity\AppTranslation;
use App\Form\TranslationForm;
use App\Entity\Locale;
use App\Service\LogService;

class AppTranslationController extends Controller
{
    /**
     * @Route("/{_locale}/admin/apptranslation/", name="apptranslation"))
     */
     final public function list(TranslatorInterface $translator)
     {

         return $this->render('apptranslation/admin/list.html.twig', array(
             'page_title' => $translator->trans('App Translations'),
             'can_add' => true,
             'can_edit' => true,
         ));
     }

     /**
      * @Route("/{_locale}/admin/apptranslation/get/", name="apptranslation_get"))
      */
     final public function getTranslation(Request $request)
     {
         $sort_column = $request->request->get('sortColumn', 'id');
         $sort_direction = strtoupper($request->request->get('sortDirection', 'desc'));
         $limit = $request->request->get('limit', 15);
         $offset = $request->request->get('offset', 0);
         $filter = $request->request->get('filter', '');

         $where = array();
         $whereString = '1=1';
         $filter = explode('&', $filter);
         if (!empty($filter))
         foreach($filter as $filter_item) {
             $filter_item = explode('=', $filter_item);
             if (!empty($filter_item[1])) {
                 $where[$filter_item[0]] = $filter_item[1];
                 $whereString .= " AND `" . $filter_item[0] . "`='" . $filter_item[1] . "'";
             }
         }

         $qb = $this->getDoctrine()->getRepository(AppTranslation::class)->createQueryBuilder('t');
         $qb->select('count(t.id)');
         $qb->where($whereString);
         $count = $qb->getQuery()->getSingleScalarResult();

         if (empty($limit)) {
             $pages = $this->getDoctrine()
                 ->getRepository(AppTranslation::class)
                 ->findTranslationsList();
         } else {
             $pages = $this->getDoctrine()
                 ->getRepository(AppTranslation::class)
                 ->findTranslationsList($where, array($sort_column => $sort_direction), $limit, $offset);
         }

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);

         $json = array(
             'total' => 6,
             'data' => $pages
         );

         $json = $serializer->serialize($json, 'json');

         return $this->json($json);
     }

     /**
      * @Route("/{_locale}/admin/apptranslation/add/", name="apptranslation_add"))
      * @Route("/{_locale}/admin/apptranslation/edit/{id}/", name="apptranslation_edit"))
      */
    final public function edit($id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        //$this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, 'Unable to access this page!');

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $translation = $this->getDoctrine()
                ->getRepository(AppTranslation::class)
                ->find($id);
            if (!$translation) {
                $translation = new AppTranslation();
                $this->addFlash(
                    'error',
                    $translator->trans('The requested setting does not exist!')
                );
                $id = 0; // Set ID to 0, will be used later on
            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $serializer->serialize($translation, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';

            }
        } else {
            $translation = new AppTranslation();
        }

        $locales = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->findActiveLocales();

        $form = $this->createFormBuilder();
        $disabled = false;
        if (!empty($translation->getOriginal())) $disabled = true;
        $form->add('original', TextType::class, array('label' => 'Original text', 'data' => $translation->getOriginal(), 'disabled' => $disabled));

        foreach($locales as $locale) {

            $fieldId = 'translation';
            if (empty($locale->getDefault())) $fieldId = 'translation_' . $locale->getLocale();

            $fieldTranslation = $this->getDoctrine()
                ->getRepository(AppTranslation::class)
                ->findTranslation($translation->getOriginal(), $locale->getId());

            $fieldValue = '';
            if ($fieldTranslation && !empty($fieldTranslation->getTranslation())) $fieldValue = $fieldTranslation->getTranslation();

            $form->add($fieldId, TextType::class, array('label' => 'Translation (' . strtoupper($locale->getLocale()) . ')', 'data' => $fieldValue, 'required' => false));
        }
        $form->add('save', SubmitType::class, array('label' => 'Save'));

        $form = $form->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // perform some action...
            $formData = $form->getData();

            if (!empty($id)) {
                $fieldTranslation = $this->getDoctrine()
                    ->getRepository(AppTranslation::class)
                    ->find($id);
                $original = $fieldTranslation->getOriginal();
            } else {
                $original = $formData['original'];
            }

            foreach($locales as $locale) {

                $fieldTranslation = $this->getDoctrine()
                    ->getRepository(AppTranslation::class)
                    ->findTranslation($translation->getOriginal(), $locale->getId());

                if (!$fieldTranslation) {
                    $fieldTranslation = new AppTranslation();
                }

                $fieldId = 'translation';
                if (empty($locale->getDefault())) $fieldId = 'translation_' . $locale->getLocale();

                $fieldTranslation->setLocale($locale);
                $fieldTranslation->setOriginal($original);
                $fieldTranslation->setTranslation($formData[$fieldId]);

                $em = $this->getDoctrine()->getManager();
                $em->persist($fieldTranslation);
                $em->flush();
            }

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($translation, 'json');
            $log->add('AppTranslation', $id, $logMessage, $logComment);

            $this->addFlash(
                'success',
                $translator->trans('Your changes were saved!')
            );

            return $this->redirectToRoute('apptranslation_edit', array('id' => $id));
        }

        if (!empty($id)) $title = $translator->trans('Edit translation');
        else $title = $translator->trans('Add translation');

        return $this->render('common/form.html.twig', array(
            'form' => $form->createView(),
            'page_title' => $title,
        ));
     }

    /**
     * @Route("/{_locale}/admin/apptranslation/export/", name="apptranslation_export"))
     */
    final public function export(TranslatorInterface $translator, LogService $log)
    {
        $translations = $this->getDoctrine()
                ->getRepository(AppTranslation::class)
                ->getExport();

        $locales = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->findAll();

        $response = new StreamedResponse();
        $response->setCallback(function() use ($translator, $translations, $locales) {

            $handle = fopen('php://output', 'w');

            $columnNames = array(
                $translator->trans('ID'),
                $translator->trans('Original'),
            );

            if ($locales) {
                foreach($locales as $locale) {
                    $columnNames[] = $locale->getLocale();
                }
            }

            fputcsv($handle, $columnNames, ';');

            foreach($translations as $translation) {

                $row = array();
                $row[] = $translation['id'];
                $row[] = $translation['original'];

                if ($locales) {
                    foreach($locales as $locale) {
                        $row[] = $translation[$locale->getLocale()];
                    }
                }

                fputcsv($handle, $row,';');
            }

            fclose($handle);
        });

        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="translation_export_' . date('YmdHis') . '.csv"');

        return $response;


    }
}
