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
use App\Entity\Permission;
use App\Entity\PermissionGroup;
use App\Entity\Setting;
use App\Entity\Role;
use App\Entity\RolePermission;
use App\Service\LogService;
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
            ->getRepository(Page::class)
            ->findOneBy(array('locale' => $localeEntity->getId(), 'pageRoute' => $routeName));

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
            'publish_date' => $page->getPublishDate(),
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
     * @Route("/{_locale}/admin/page/", name="page_admin"))
     */
    final public function list()
    {
        return $this->render('page/admin/list.html.twig', array(
            'can_add' => true,
            'can_edit' => true,
            'can_delete' => true,
        ));
    }

     /**
      * @Route("/{_locale}/admin/page/get/", name="page_get"))
      */
    final public function getPage()
    {
        $request = Request::createFromGlobals();
        $sort_column = $request->request->get('sortColumn', 'pageTitle');
        $sort_direction = strtoupper($request->request->get('sortDirection', 'asc'));
        $limit = $request->request->get('limit', 15);
        $offset = $request->request->get('offset', 0);
        $filter = $request->request->get('filter', '');

        $locale = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->findOneBy(array('locale' => $this->container->getParameter('kernel.default_locale')));

        $where = array('locale' => $locale->getId());
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

        $qb = $this->getDoctrine()->getRepository(Page::class)->createQueryBuilder('p');
        $qb->select('count(p.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        if (empty($limit)) {
            $pages = $this->getDoctrine()
                ->getRepository(Page::class)
                ->findBy($where, array($sort_column => $sort_direction));
        } else {
            $pages = $this->getDoctrine()
                ->getRepository(Page::class)
                ->findBy($where, array($sort_column => $sort_direction), $limit, $offset);
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
      * @Route("/{_locale}/admin/page/add/", name="page_add"))
      * @Route("/{_locale}/admin/page/edit/{id}/", name="page_edit"))
      * @Route("/{_locale}/admin/page/edit/{id}/translate/{localeId}/", name="page_edit_translate"))
      */
    final public function edit($id=0, $localeId=0, Request $request, TranslatorInterface $translator, LogService $log, KernelInterface $kernel)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $logMessage = '';
        $logComment = 'Insert';
        $urlLocale = '';

        if (empty($localeId)) {
            $localeSlug = $this->container->getParameter('kernel.default_locale');
            $locale = $this->getDoctrine()
                ->getRepository(Locale::class)
                ->findOneBy(array('locale' => $localeSlug));
            if ($locale) $localeId = $locale->getId();
        }

        if (empty($locale)) {
            $locale = $this->getDoctrine()
                ->getRepository(Locale::class)
                ->find($localeId);
        }

        if (empty($locale->getDefault())) $urlLocale = $locale->getLocale() . '/';

        if (!empty($id)) {

            if (!$locale->getDefault()) {

                $page = $this->getDoctrine()
                    ->getRepository(Page::class)
                    ->findOneBy(array('defaultId' => $id, 'locale' => $localeId));

                if (!$page) $page = new Page();

                $page->setDefaultId($id);

            } else {

                $page = $this->getDoctrine()
                    ->getRepository(Page::class)
                    ->find($id);

                if (!$page) {
                    $page = new Page();
                    $this->addFlash(
                        'error',
                        $translator->trans('The requested page does not exist!')
                    );
                }
            }

            if ($page) {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $serializer->serialize($page, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';
            }

        } else {
            $page = new Page();
        }

        $page->setLocale($locale);

        // Get anonymous role
        $role = $this->getDoctrine()
            ->getRepository(Role::class)
            ->findBy(array('name' => 'Anonymous'));

        // Create anonymous role
        if (!$role) {
            $role = new PermissionGroup();
            $role->setName('Anonymous');
            $role->setDescription('Anonymous');
            $em = $this->getDoctrine()->getManager();
            $em->persist($role);
            $em->flush();
        }

        $form = $this->createFormBuilder();
        $form = $form->getForm();
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {

            if (!$locale->getDefault() && empty($id)) {

                $localeSlugDefault = $this->container->getParameter('kernel.default_locale');
                $localeDefault = $this->getDoctrine()
                    ->getRepository(Locale::class)
                    ->findOneBy(array('locale' => $localeSlugDefault));
                $pageDefault = new Page();
                $pageDefault->setLocale($localeDefault);
                $pageDefault->setPageTitle($request->request->get('page-title', ''));
                $pageDefault->setPageRoute($request->request->get('page-route', ''));
                $pageDefault->setContent($request->request->get('page-content', ''));
                $pageDefault->setMetaTitle($request->request->get('page-meta-title', ''));
                $pageDefault->setMetaKeywords($request->request->get('page-meta-keywords', ''));
                $pageDefault->setMetaDescription($request->request->get('page-meta-description', ''));
                $pageDefault->setMetaCustom($request->request->get('page-meta-custom', ''));
                $pageDefault->setPublishDate(new \DateTime($request->request->get('page-publish-date', '')));

                $expireDate = $request->request->get('page-expire-date', '');
                if (!empty($expireDate)) $pageDefault->setExpireDate(new \DateTime($expireDate));
                else $pageDefault->setExpireDate(NULL);

                $pageDefault->setStatus($request->request->get('page-status', 1));
                $pageDefault->setPageWeight($request->request->get('page-weight', 1));
                $pageDefault->setPageWidth($request->request->get('page-width', ''));
                $pageDefault->setDisableLayout($request->request->get('disable-layout', 0));

                $pageDefault->setCustomCss($request->request->get('custom-css', ''));
                $pageDefault->setCustomJs($request->request->get('custom-js', ''));
                $pageDefault->setLastModified(new \DateTime());
                $em = $this->getDoctrine()->getManager();
                $em->persist($pageDefault);
                $em->flush();
                $id = $pageDefault->getId();
                $page->setDefaultId($id);
            }

            $page->setPageTitle($request->request->get('page-title', ''));
            $page->setPageRoute($request->request->get('page-route', ''));
            $page->setContent($request->request->get('page-content', ''));
            $page->setMetaTitle($request->request->get('page-meta-title', ''));
            $page->setMetaKeywords($request->request->get('page-meta-keywords', ''));
            $page->setMetaDescription($request->request->get('page-meta-description', ''));
            $page->setMetaCustom($request->request->get('page-meta-custom', ''));

            // Image

            $page->setPublishDate(new \DateTime($request->request->get('page-publish-date', '')));

            $expireDate = $request->request->get('page-expire-date', '');
            if (!empty($expireDate)) $page->setExpireDate(new \DateTime($expireDate));
            else $page->setExpireDate(NULL);

            $page->setStatus($request->request->get('page-status', 1));
            $page->setPageWeight($request->request->get('page-weight', 1));
            $page->setPageWidth($request->request->get('page-width', ''));
            $page->setDisableLayout($request->request->get('disable-layout', 0));

            $page->setCustomCss($request->request->get('custom-css', ''));
            $page->setCustomJs($request->request->get('custom-js', ''));
            $page->setLastModified(new \DateTime());

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($page, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();
            if (empty($id)) $id = $page->getId();

            $log->add('Page', $page->getId(), $logMessage, $logComment);

            // Get permission group
            $permissionGroup = $this->getDoctrine()
                ->getRepository(PermissionGroup::class)
                ->findOneBy(array('name' => 'Pages'));

            // Create permission group
            if (!$permissionGroup) {
                $permissionGroup = new PermissionGroup();
                $permissionGroup->setName('Pages');
                $em = $this->getDoctrine()->getManager();
                $em->persist($permissionGroup);
                $em->flush();
            }

            $permissionGroupId = $permissionGroup->getId();

            // Check permission
            $permission = $this->getDoctrine()
                ->getRepository(Permission::class)
                ->findOneBy(array('pageId' => $page->getId()));

            // Create permission group
            if (!$permission) $permission = new Permission();

            $permission->setRouteName('page_' . strtolower(str_replace('/', '_', $page->getPageRoute())));
            $permission->setDescription($page->getPageTitle());
            $permission->setGroupId($permissionGroupId);
            $permission->setPageId($page->getId());
            $em = $this->getDoctrine()->getManager();
            $em->persist($permission);
            $em->flush();

            $permissionId = $permission->getId();

            $setRoles = $this->getDoctrine()
                ->getRepository(RolePermission::class)
                ->findBy(array('permissionId' => $permission->getId()));

            foreach($setRoles as $setRole) {
                $em->remove($setRole);
            }
            $em->flush();

            $formRoles = $request->request->get('form_role', '');

            if (!empty($formRoles)) {
                foreach($formRoles as $formRole => $roleId) {
                    $userRole = new RolePermission();
                    $userRole->setPermissionId($permissionId);
                    $userRole->setRoleId($roleId);
                    $em->persist($userRole);
                }
                $em->flush();
            }

            $this->addFlash(
                'success',
                $translator->trans('Your changes were saved!')
            );

            $application = new Application($kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput(array('command' => 'cache:clear'));
            $output = new NullOutput();
            $application->run($input, $output);

            if (!empty($localeId)) return $this->redirectToRoute('page_edit_translate', array('id' => $id, 'localeId' => $localeId));
            else return $this->redirectToRoute('page_edit', array('id' => $id));
        }

        // https://symfony.com/doc/current/security/csrf.html
        //if ($this->isCsrfTokenValid('edit-page', $submittedToken)) {
        // ... do something, like deleting an object
        //}


        $permission = $this->getDoctrine()
            ->getRepository(Permission::class)
            ->findOneBy(array('pageId' => $id));

        $roles = $this->getDoctrine()
            ->getRepository(Role::class)
            ->findAll();

        $locales = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->findAll();

        if ($permission) {
            $setRoles = $this->getDoctrine()
                ->getRepository(RolePermission::class)
                ->findBy(array('permissionId' => $permission->getId()));
        } else {
            $setRoles = array();
        }

        if (!empty($id)) $title = $translator->trans('Edit page');
        else $title = $translator->trans('Add page');

        return $this->render('page/admin/edit.html.twig', array(
            'page_title' => $title,
            'id' => $id,
            'edit_page_title' => $page->getPageTitle(),
            'page_route' => $page->getPageRoute(),
            'content' => $page->getContent(),
            'meta_title' => $page->getMetaTitle(),
            'meta_keywords' => $page->getMetaKeywords(),
            'meta_description' => $page->getMetaDescription(),
            'meta_custom' => $page->getMetaCustom(),
            'publish_date' => $page->getPublishDate(),
            'expire_date' => $page->getExpireDate(),
            'status' => $page->getStatus(),
            'locale' => $locale,
            'url_locale' => $urlLocale,
            'page_width' => $page->getPageWidth(),
            'page_weight' => $page->getPageWeight(),
            'disable_layout' => $page->getDisableLayout(),
            'authorization' => '',
            'main_image' => $page->getMainImage(),
            'custom_css' => $page->getCustomCss(),
            'custom_js' => $page->getCustomJs(),
            'roles' => $roles,
            'roles_set' => $setRoles,
            'default_locale' => $this->container->getParameter('kernel.default_locale'),
            'locales' => $locales,
        ));
    }

     /**
      * @Route("/{_locale}/admin/page/delete/{id}/", name="page_delete"))
      */
    final public function delete($id, LogService $log)
    {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository(Page::class)->find($id);

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $logMessage = '<i>Data:</i><br>';
        $logMessage .= $serializer->serialize($page, 'json');

        $log->add('Page', $id, $logMessage, 'Delete');

        $em->remove($page);
        $em->flush();

        return new Response(
            '1'
        );
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
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . PHP_EOL;

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
