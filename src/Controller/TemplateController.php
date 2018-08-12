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

            $template->setFooter($request->request->get('template-footer', ''));
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
            'template_footer' => $template->getFooter(),
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
                'vendor/twbs/bootstrap/scss',
                'templates/layout/' . $template->getTag() . '/scss',
            ));
            $css = $scss->compile(file_get_contents('vendor/components/css-reset/reset.min.css'));

            $css .= $scss->compile('@import "build.scss";');

            $css .= $scss->compile(file_get_contents('vendor/daneden/animate.css/animate.min.css'));
            $css .= $scss->compile('@import "index.scss";');
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
            'assets/js/admin.js' => 'public/js/admin.js',
            'assets/js/common.js' => 'public/js/common.js',
            'assets/js/list.js' => 'public/js/list.js',
            'assets/js/table.js' => 'public/js/table.js',
            'assets/js/main.js' => 'public/js/main.js',
            'assets/js/modal.js' => 'public/js/modal.js',
            'assets/js/dymo-print.js' => 'public/js/dymo-print.js',
            'assets/js/paginator.js' => 'public/js/paginator.js',
            'assets/js/simple-cookie-bar.js' => 'public/js/simple-cookie-bar.js',
            'assets/js/usability.js' => 'public/js/usability.js',
            'assets/vendor/popper.min.js' => 'public/vendor/popper.min.js',
            'assets/vendor/edit_area' => 'public/vendor/edit_area',
            'assets/vendor/datetimepicker/build' => 'public/vendor/datetimepicker',
            'assets/vendor/signature_pad/docs/js/signature_pad.umd.js' => 'public/vendor/signature-pad/signature-pad.js',
            'assets/vendor/dymo/DYMO.Label.Framework.2.0.2.js' => 'public/vendor/dymo-framework.js',
            'assets/vendor/JsBarcode.all.min.js' => 'public/vendor/JsBarcode.all.min.js',
            'vendor/components/jquery/jquery.min.js' => 'public/vendor/jquery/jquery.min.js',
            'vendor/components/jquery/jquery.min.map' => 'public/vendor/jquery/jquery.min.map',
            'vendor/twbs/bootstrap/dist/js/bootstrap.min.js' => 'public/vendor/bootstrap/bootstrap.min.js',
            'vendor/twbs/bootstrap/dist/js/bootstrap.min.js.map' => 'public/vendor/bootstrap/bootstrap.min.js.map',
            'vendor/bassjobsen/bootstrap-3-typeahead/bootstrap3-typeahead.min.js' => 'public/vendor/typeahead/bootstrap3-typeahead.min.js',
            'vendor/jackocnr/intl-tel-input/build' => 'public/vendor/intl-tel-input',
            'vendor/tinymce/tinymce' => 'public/vendor/tinymce',
            'vendor/moment/moment/min' => 'public/vendor/moment',
            'vendor/nnnick/chartjs/dist' => 'public/vendor/chartjs',
            'vendor/components/font-awesome/webfonts/fa-brands-400.eot' => 'public/vendor/font-awesome/fonts/fa-brands-400.eot',
            'vendor/components/font-awesome/webfonts/fa-brands-400.svg' => 'public/vendor/font-awesome/fonts/fa-brands-400.svg',
            'vendor/components/font-awesome/webfonts/fa-brands-400.ttf' => 'public/vendor/font-awesome/fonts/fa-brands-400.ttf',
            'vendor/components/font-awesome/webfonts/fa-brands-400.woff' => 'public/vendor/font-awesome/fonts/fa-brands-400.woff',
            'vendor/components/font-awesome/webfonts/fa-brands-400.woff2' => 'public/vendor/font-awesome/fonts/fa-brands-400.woff2',
            'vendor/components/font-awesome/webfonts/fa-regular-400.eot' => 'public/vendor/font-awesome/fonts/fa-regular-400.eot',
            'vendor/components/font-awesome/webfonts/fa-regular-400.svg' => 'public/vendor/font-awesome/fonts/fa-regular-400.svg',
            'vendor/components/font-awesome/webfonts/fa-regular-400.ttf' => 'public/vendor/font-awesome/fonts/fa-regular-400.ttf',
            'vendor/components/font-awesome/webfonts/fa-regular-400.woff' => 'public/vendor/font-awesome/fonts/fa-regular-400.woff',
            'vendor/components/font-awesome/webfonts/fa-regular-400.woff2' => 'public/vendor/font-awesome/fonts/fa-regular-400.woff2',
            'vendor/components/font-awesome/webfonts/fa-solid-900.eot' => 'public/vendor/font-awesome/fonts/fa-solid-900.eot',
            'vendor/components/font-awesome/webfonts/fa-solid-900.svg' => 'public/vendor/font-awesome/fonts/fa-solid-900.svg',
            'vendor/components/font-awesome/webfonts/fa-solid-900.ttf' => 'public/vendor/font-awesome/fonts/fa-solid-900.ttf',
            'vendor/components/font-awesome/webfonts/fa-solid-900.woff' => 'public/vendor/font-awesome/fonts/fa-solid-900.woff',
            'vendor/components/font-awesome/webfonts/fa-solid-900.woff2' => 'public/vendor/font-awesome/fonts/fa-solid-900.woff2',
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
