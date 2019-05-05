<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\TranslatorInterface;

use App\Entity\Locale;
use App\Entity\Translation;
use App\Service\LogService;

class AppTranslationController extends Controller
{
    /**
    * @Route("/{_locale}/admin/apptranslation/export/", name="admin_apptranslation_export"))
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
