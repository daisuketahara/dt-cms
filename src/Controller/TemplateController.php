<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Leafo\ScssPhp\Compiler;

use App\Entity\Template;
use App\Service\LogService;

class TemplateController extends Controller
{
    /**
     * @Route("/{_locale}/admin/template/", name="template"))
     */
     final public function list(TranslatorInterface $translator)
     {
         $templates = $this->getDoctrine()
             ->getRepository(Template::class)
             ->findAll();

         return $this->render('template/admin/list.html.twig', array(
             'page_title' => $translator->trans('Templates'),
             'templates' => $templates,
         ));
     }

     /**
      * @Route("/{_locale}/admin/template/edit/{id}/", name="template_edit"))
      */
    public function edit($id, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $template = $this->getDoctrine()
            ->getRepository(Template::class)
            ->find($id);

        $form = $this->createFormBuilder();
        $form = $form->getForm();
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {

            $template->setCustomCss($request->request->get('custom-css', ''));
            $template->setCustomJs($request->request->get('custom-js', ''));

            $em = $this->getDoctrine()->getManager();
            $em->persist($template);
            $em->flush();

            $this->addFlash(
                'success',
                $translator->trans('Your changes were saved!')
            );
        }

        return $this->render('template/admin/edit.html.twig', array(
            'page_title' => $translator->trans('Edit template'),
            'templateId' => $template->getId(),
            'custom_css' => $template->getCustomCss(),
            'custom_js' => $template->getCustomJs(),
        ));
    }

     /**
      * @Route("/{_locale}/admin/template/compile/{id}/", name="template_compile"))
      */
    public function compile($id, TranslatorInterface $translator, LogService $log, Filesystem $fileSystem)
    {
        ini_set('max_execution_time', 300);

        $template = $this->getDoctrine()
            ->getRepository(Template::class)
            ->find($id);

        try {
			$scss = new Compiler();
            $scss->setFormatter('Leafo\\ScssPhp\\Formatter\\Crunched');
            $scss->setImportPaths(array(
                'assets/scss/',
                'assets/vendor/',
                'vendor',
                'vendor/twbs/bootstrap/scss',
                'templates/layout/' . $template->getTag() . '/scss',
            ));

            $css = $scss->compile('@import "index.scss";');
            $css .= $scss->compile($template->getCustomCss());

            if (file_exists('public/css/' . $template->getTag() . '.css')) unlink('public/css/' . $template->getTag() . '.css');
			$myfile = fopen('public/css/' . $template->getTag() . '.css', 'w');
			fwrite($myfile, $css);
			fclose($myfile);
			chmod('public/css/' . $template->getTag() . '.css', 0644);

            $build_style = 1;

        } catch(Exception $e) {
        	$build_style = $e->getMessage();
        }

        // Create symlinks
        // https://symfony.com/doc/current/components/filesystem.html
        $symlinks = array(
            'assets/js/app.min.js' => 'public/js/app.js',
            'assets/js/admin.min.js' => 'public/js/admin.js',
            'assets/js/common.min.js' => 'public/js/common.js',
            'assets/js/contact-form.min.js' => 'public/js/contact-form.js',
            'assets/js/list.min.js' => 'public/js/list.js',
            'assets/js/table.min.js' => 'public/js/table.js',
            'assets/js/main.min.js' => 'public/js/main.js',
            'assets/js/modal.min.js' => 'public/js/modal.js',
            'assets/js/dymo-print.min.js' => 'public/js/dymo-print.js',
            'assets/js/paginator.min.js' => 'public/js/paginator.js',
            'assets/js/simple-cookie-bar.min.js' => 'public/js/simple-cookie-bar.js',
            'assets/js/usability.min.js' => 'public/js/usability.js',
            'assets/vendor/popper.min.js' => 'public/vendor/popper.min.js',
            'assets/vendor/edit_area' => 'public/vendor/edit_area',
            'assets/vendor/onsen-ui' => 'public/vendor/onsen-ui',
            'assets/vendor/datetimepicker/build' => 'public/vendor/datetimepicker',
            'assets/vendor/intl-tel-input/build' => 'public/vendor/intlTelInput',
            'assets/vendor/signature_pad/docs/js/signature_pad.umd.js' => 'public/vendor/signature-pad/signature-pad.js',
            'assets/vendor/dymo/DYMO.Label.Framework.2.0.2.js' => 'public/vendor/dymo-framework.js',
            'assets/vendor/JsBarcode.all.min.js' => 'public/vendor/JsBarcode.all.min.js',
            'assets/vendor/google-places.js' => 'public/vendor/google-places.js',
            'assets/vendor/fontawesome/webfonts' => 'public/vendor/fontawesome/webfonts',
            'assets/vendor/intl-tel-input-14.0.0/build' => 'public/vendor/intl-tel-input',
            'vendor/components/jquery/jquery.min.js' => 'public/vendor/jquery/jquery.min.js',
            'vendor/components/jquery/jquery.min.map' => 'public/vendor/jquery/jquery.min.map',
            'vendor/daneden/animate.css/animate.min.css' => 'public/vendor/animate.min.css',
            'vendor/twbs/bootstrap/dist/js/bootstrap.min.js' => 'public/vendor/bootstrap/bootstrap.min.js',
            'vendor/twbs/bootstrap/dist/js/bootstrap.min.js.map' => 'public/vendor/bootstrap/bootstrap.min.js.map',
            'vendor/bassjobsen/bootstrap-3-typeahead/bootstrap3-typeahead.min.js' => 'public/vendor/typeahead/bootstrap3-typeahead.min.js',
            'vendor/jackocnr/intl-tel-input/build' => 'public/vendor/intl-tel-input',
            'vendor/summernote/summernote/dist' => 'public/vendor/summernote',
            'vendor/moment/moment/min' => 'public/vendor/moment',
            'vendor/nnnick/chartjs/dist' => 'public/vendor/chartjs',
        );

        if ($fileSystem->exists('public/js')) $fileSystem->mkdir('public/js', 0644);
        if ($fileSystem->exists('public/vendor')) $fileSystem->mkdir('public/vendor', 0644);

        $path = getcwd() . '/';

        if (!empty($symlinks))
        foreach($symlinks as $target => $link) {
            if (file_exists($path.$link)) if (is_link($path.$link)) unlink($path.$link);
            if (!file_exists($path.$link)) $fileSystem->symlink($path.$target, $path.$link, true);
        }

        $this->addFlash(
            'success',
            $translator->trans('CSS file compiled!')
        );

        return $this->redirectToRoute('template_edit', array('id' => $id));
    }
}
