<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;;
use Leafo\ScssPhp\Compiler;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;

use App\Entity\Locale;
use App\Entity\Page;
use App\Entity\PageContent;
use App\Entity\Permission;
use App\Entity\PermissionGroup;
use App\Entity\Setting;
use App\Entity\Role;
use App\Entity\RolePermission;
use App\Service\LogService;
use App\Service\SettingService;
use App\Service\RedirectService;

class PageController extends Controller
{
    public function loadPage(Request $request, RedirectService $redirect)
    {
        $route = $request->attributes->get('_route');
        //var_dump($route);exit;
        $route = explode('_', $route);
        $locale = $route[1];
        $routeName = $route[2];

        $request->setLocale($locale);
        $localeEntity = $this->getDoctrine()
        ->getRepository(Locale::class)
        ->findOneBy(array('locale' => $locale));

        $page = $this->getDoctrine()
        ->getRepository(PageContent::class)
        ->findOneBy(array('locale' => $localeEntity, 'pageRoute' => $routeName));

        if (!$page) {
            throw $this->createNotFoundException('The page does not exist');
        }

        $metaTitle = $page->getMetaTitle();
        if (empty($metaTitle)) $metaTitle = $page->getPageTitle();

        $customCss = $page->getCustomCss();
        try {
            $scss = new Compiler();
            $scss->setFormatter('Leafo\\ScssPhp\\Formatter\\Crunched');
            $css = $scss->compile($customCss);
        } catch(Exception $e) {
            $css = $customCss;
        }

        return $this->render('page/page.html.twig', array(
            'page_title' => $page->getPageTitle(),
            'content' => $page->getContent(),
            'meta_title' => $metaTitle,
            'meta_keywords' => $page->getMetaKeywords(),
            'meta_description' => $page->getMetaDescription(),
            'meta_custom' => $page->getMetaCustom(),
            'publish_date' => $page->getPage()->getPublishDate(),
            'page_width' => $page->getPageWidth(),
            'disable_layout' => $page->getDisableLayout(),
            'main_image' => $page->getMainImage(),
            'custom_css' => $css,
            'custom_js' => $page->getCustomJs(),
        ));
    }

    final public function notfound()
    {
        throw $this->createNotFoundException('The file does not exist');
        return $this->render('page/404.html.twig');
    }

    /**
    * @Route("/{_locale}/admin/page/", name="admin_page_list"))
    */
    final public function list(TranslatorInterface $translator)
    {
        return $this->render('page/admin/list.html.twig', array(
            'apikey' => 'ce07f59f2eca96d9e3e4dbe2becce743',
            'page_title' => $translator->trans('Pages'),
        ));
    }

    /**
    * @Route("/{_locale}/admin/page/insert/", name="admin_page_insert"))
    * @Route("/{_locale}/admin/page/update/{id}/", name="admin_page_update"))
    */
    final public function edit($id=0, TranslatorInterface $translator, settingService $setting)
    {
        if (!empty($id)) $title = $translator->trans('Edit page');
        else $title = $translator->trans('Add page');

        return $this->render('page/admin/edit.html.twig', array(
            'apikey' => 'ce07f59f2eca96d9e3e4dbe2becce743',
            'page_title' => $title,
            'page_id' => $id,
            'domain' => $setting->getSetting('site.url'),
        ));
    }

    /**
    * @Route("/{_locale}/page-not-found/", name="page_not_found"))
    */
    public function pageNotFound(Request $request)
    {
        return $this->render('page/404.html.twig');
    }

    /**
    * @Route("/{_locale}/access-denied/", name="page_access_denied"))
    */
    public function accessDenied(Request $request)
    {
        return $this->render('page/401.html.twig');
    }

    /**
    * @Route("/cron/generate-sitemap/", name="page_generate_sitemap"))
    */
    public function generateSitemap(Request $request)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . PHP_EOL;

        $defaultLocale = $this->getDoctrine()
        ->getRepository(Locale::class)
        ->findBy(array('default' => true));

        $locales = $this->getDoctrine()
        ->getRepository(Locale::class)
        ->findAll();

        $pages = $this->getDoctrine()
        ->getRepository(Page::class)
        ->findBy(array('status' => 1, 'locale' => $defaultLocale));

        $setting = $this->getDoctrine()
        ->getRepository(Setting::class)
        ->findOneBy(array('settingKey' => 'site.url'));
        $domain = $setting->getSettingValue();

        if ($pages) {
            foreach($pages as $page) {

                $pageRoute = $page->getPageRoute();
                if (!empty($pageRoute)) $pageRoute .= '/';

                $xml .= '<url>' . PHP_EOL;
                $xml .= '<loc>' . $domain . '/' . $pageRoute . '</loc>' . PHP_EOL;
                $xml .= '<lastmod>' . $page->getLastModified()->format('Y-m-d') . '</lastmod>' . PHP_EOL;
                $xml .= '<changefreq>monthly</changefreq>' . PHP_EOL;
                $xml .= '<priority>' . round(($page->getPageWeight()/10),1) . '</priority>' . PHP_EOL;

                if ($locales) {
                    foreach($locales as $locale) {

                        if (empty($locale->getDefault())) {

                            $translatedPage = $this->getDoctrine()
                            ->getRepository(Page::class)
                            ->findOneBy(array('defaultId' => $page->getId(), 'status' => 1, 'locale' => $locale));

                            if ($translatedPage) {
                                $translatedPageRoute = $translatedPage->getPageRoute();
                                if (!empty($pageRoute)) $translatedPageRoute .= '/';
                                $xml .= '<xhtml:link rel="alternate" hreflang="' . $locale->getLocale() . '" href="' . $domain . '/' . $locale->getLocale()  . '/' . $translatedPageRoute . '"/>' . PHP_EOL;
                            }

                        } else {
                            $xml .= '<xhtml:link rel="alternate" hreflang="' . $locale->getLocale() . '" href="' . $domain . '/' . $pageRoute . '"/>' . PHP_EOL;
                        }
                    }
                }
                $xml .= '</url>' . PHP_EOL;
            }
        }
        $xml .= '</urlset>';

        if (file_exists('public/sitemap.xml')) unlink('public/sitemap.xml');
        $sitemap = fopen('public/sitemap.xml', 'w');
        fwrite($sitemap, $xml);
        fclose($sitemap);

        echo '1';
        exit;
    }
}
