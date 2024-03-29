<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Yaml\Yaml;

use Leafo\ScssPhp\Compiler;

use Symfony\Bundle\FrameworkBundle\Console\Application;
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
use App\Service\RedirectService;

class PageController extends AbstractController
{
    /**
    * @Route("/api/v1/page/info/", name="api_page_info"), methods={"GET","HEAD"})
    */
    final public function info(Request $request, TranslatorInterface $translator)
    {
        $properties = Yaml::parseFile('src/Config/page.yaml');

        $api = [];
        $settings = ['title' => 'pages'];

        if (!empty($properties['actions'])) {
            foreach($properties['actions'] as $key => $action) {
                if (!empty($action['api'])) $api[$key] = $action['api'];
                elseif (!empty($action['url'])) $settings[$key] = $action['url'];
            }
        }

        $info = array(
            'api' => $api,
            'settings' => $settings,
            'fields' => $properties['fields'],
        );

        if (!empty($properties['buttons'])) $info['buttons'] = $properties['buttons'];

        return $this->json($info);
    }

    /**
    * @Route("/api/v1/page/list/", name="api_page_list"), methods={"GET","HEAD"})
    */
    final public function list(Request $request)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['sort'])) $sort_column = $params['sort']; else $sort_column = 'id';
        if (!empty($params['dir'])) $sort_direction = $params['dir']; else $sort_direction = 'desc';
        if (!empty($params['limit'])) $limit = $params['limit']; else $limit = 15;
        if (!empty($params['offset'])) $offset = $params['offset']; else $offset = 0;
        if (!empty($params['filter'])) $filter = $params['filter'];
        if (!empty($params['locale'])) $localeId = $params['locale'];

        $locale = $this->getDoctrine()
        ->getRepository(Locale::class)
        ->find($localeId);

        $whereString = 'l.locale='. $locale->getId();
        if (!empty($filter)) {
            parse_str($filter, $filter_array);
            foreach($filter_array as $key => $value) {
                if (!empty($value)) {
                    //$where[$key] = $value;
                    $whereString .= " AND l." . $key . " LIKE '%" . $value . "%'";
                }
            }
        }

        $qb = $this->getDoctrine()->getRepository(PageContent::class)->createQueryBuilder('l');
        $qb->select('count(l.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->getDoctrine()->getRepository(PageContent::class)->createQueryBuilder('l');
        $qb->select();
        $qb->where($whereString);
        $qb->orderBy('l.'.$sort_column, $sort_direction);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $pages = $qb->getQuery()->getResult();

        $json = array(
            'total' => $count,
            'data' => $pages,
        );

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/page/get/{id}/", name="api_page_get"), methods={"GET","HEAD"})
    */
    final public function getPage(int $id, Request $request)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['locale'])) {
            $localeId = $params['locale'];
            $locale = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->find($localeId);
        }

        if (!empty($id)) {

            $page = $this->getDoctrine()
                ->getRepository(PageContent::class)
                ->find($id);

            if (!empty($localeId) && $localeId != $page->getLocale()->getId()) {
                $otherPage = $this->getDoctrine()
                    ->getRepository(PageContent::class)
                    ->findOneBy(['locale' => $locale, 'page' => $page->getPage()]);
                if ($otherPage) $page = $otherPage;
                else $page = true;
            }

            if ($page) {

                // Check page permission
                $permission = $this->getDoctrine()
                    ->getRepository(Permission::class)
                    ->findOneBy(array('page' => $page->getPage()));

                $pageRoles = array();
                if ($permission) {
                    $roles = $this->getDoctrine()
                        ->getRepository(Permission::class)
                        ->getPermissionRoles($permission->getId());

                    if ($roles) {
                        foreach ($roles as $role) {
                            $pageRoles[$role['role_id']] = true;
                        }
                    }
                }

                $response = [
                    'success' => true,
                    'data' => $page,
                    'roles' => $pageRoles,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find page',
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Id cannot be empty',
            ];
        }

        return $this->json($response);
    }

    /**
    * @Route("/api/v1/page/insert/", name="api_page_insert", methods={"PUT"})
    * @Route("/api/v1/page/update/{id}/", name="api_page_update", methods={"PUT"})
    */
    final public function edit(int $id=0, Request $request, TranslatorInterface $translator)
    {
        $params = json_decode(file_get_contents("php://input"),true);

        if (!empty($params['locale'])) {
            $localeId = $params['locale'];
            $locale = $this->getDoctrine()
                ->getRepository(Locale::class)
                ->find($localeId);
        }

        if (empty($locale)) {
            $locale = $this->getDoctrine()
                ->getRepository(Locale::class)
                ->getDefaultLocale();
        }

        if (!empty($id)) {

            $page = $this->getDoctrine()
                ->getRepository(PageContent::class)
                ->find($id);

            if (!empty($page) && !empty($localeId) && $localeId != $page->getLocale()->getId()) {
                $otherPage = $this->getDoctrine()
                    ->getRepository(PageContent::class)
                    ->findOneBy(['locale' => $locale, 'page' => $page->getPage()]);
                if (!$otherPage) {
                    $otherPage = new PageContent();
                    $otherPage->setPage($page->getPage());
                    $otherPage->setLocale($locale);
                }
                $page = $otherPage;
            }

            if (!$page) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find page',
                ];
                return $this->json($response);

            } else {
                $message = 'Page has been updated';

            }
        }

        if ($request->isMethod('PUT')) {

            if (empty($page)) {
                $page = new PageContent();
                $message = 'Page has been inserted';

                $parent = new Page();
                $page->setPage($parent);

                if (empty($locale)) {
                    $locale= $this->getDoctrine()
                        ->getRepository(Locale::class)
                        ->getDefaultLocale();
                }
                $page->setLocale($locale);
            }

            if (isset($params['content'])) $page->setContent($params['content']);
            if (isset($params['construct'])) $page->setConstruct($this->serializer->serialize($params['construct'], 'json'));
            if (isset($params['constructCss'])) $page->setConstructCss($params['constructCss']);
            if (isset($params['customCss'])) $page->setCustomCss($params['customCss']);
            if (isset($params['customJs'])) $page->setCustomJs($params['customJs']);
            if (isset($params['disableLayout'])) $page->setDisableLayout($params['disableLayout']);
            if (isset($params['mainImage'])) $page->setMainImage($params['mainImage']);
            if (isset($params['metaCustom'])) $page->setMetaCustom($params['metaCustom']);
            if (isset($params['metaDescription'])) $page->setMetaDescription($params['metaDescription']);
            if (isset($params['metaKeywords'])) $page->setMetaKeywords($params['metaKeywords']);
            if (isset($params['metaTitle'])) $page->setMetaTitle($params['metaTitle']);
            if (isset($params['pageRoute'])) $page->setPageRoute($params['pageRoute']);

            if (!empty($params['pageTitle'])) $page->setPageTitle($params['pageTitle']);
            else $errors[] = 'Page title cannot be empty';

            if (isset($params['pageWeight'])) $page->setPageWeight($params['pageWeight']);
            if (isset($params['pageWidth'])) $page->setPageWidth($params['pageWidth']);
            if (isset($params['pageTitle'])) $page->setPageTitle($params['pageTitle']);

            if (!empty($params['publishDate'])) $page->getPage()->setPublishDate(new \DateTime($params['publishDate']));
            else $page->getPage()->setPublishDate(new \DateTime());
            // if (!empty($params['expireDate'])) $page->getPage()->setExpireDate(new \DateTime($params['expireDate']));
            if (isset($params['status'])) $page->getPage()->setStatus($params['status']);

            if (!empty($errors)) {
                $response = [
                    'success' => false,
                    'message' => $errors,
                ];
                return $this->json($response);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();
            $id = $page->getId();

            // Get permission group
            $permissionGroup = $this->getDoctrine()
                    ->getRepository(PermissionGroup::class)
                    ->findOneBy(array('name' => 'Pages'));

            // Create permission group for pages if not exists
            if (!$permissionGroup) {
                $permissionGroup = new PermissionGroup();
                $permissionGroup->setName('Pages');
                $em = $this->getDoctrine()->getManager();
                $em->persist($permissionGroup);
                $em->flush();
            }

            // Check page permission
            $permission = $this->getDoctrine()
                ->getRepository(Permission::class)
                ->findOneBy(array('page' => $page->getPage()));

            // Create permission for page if not exists
            if (!$permission) $permission = new Permission();

            $pageRoute = strtolower(str_replace('/', '_', $page->getPageRoute()));
            $pageRoute = strtolower(str_replace('-', '_', $pageRoute));

            $permission->setRouteName('page_' . $pageRoute);
            $permission->setDescription($page->getPageTitle());
            $permission->setPermissionGroup($permissionGroup);
            $permission->setPage($page->getPage());
            $permission->setComponent('Page');
            $permission->setProps('{"id": ' . $page->getPage()->getId() . '}');
            $em = $this->getDoctrine()->getManager();
            $em->persist($permission);
            $em->flush();


            if (!empty($params['role'])) $formRoles = $params['role'];
            $roles = $this->getDoctrine()
                ->getRepository(Role::class)
                ->findAll();

            foreach($roles as $role) {

                if (!empty($formRoles) && !empty($formRoles[$role->getId()])) {
                    $role->addPermission($permission);
                    $em->persist($role);
                } else {
                    $role->removePermission($permission);
                    $em->persist($role);
                }
            }
            $em->flush();

            $response = [
                'success' => true,
                'id' => $id,
                'message' => $message,
            ];
        }

        return $this->json($response);
    }

    final public function edit2(int $id=0, Request $request, TranslatorInterface $translator, KernelInterface $kernel)
    {
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

            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();
            if (empty($id)) $id = $page->getId();

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

            // Check permission
            $permission = $this->getDoctrine()
                ->getRepository(Permission::class)
                ->findOneBy(array('page' => $page));

            // Create permission group
            if (!$permission) $permission = new Permission();

            $permission->setRouteName('page_' . strtolower(str_replace('/', '_', $page->getPageRoute())));
            $permission->setDescription($page->getPageTitle());
            $permission->setPermissionGroup($permissionGroup);
            $permission->setPage($page);
            $em = $this->getDoctrine()->getManager();
            $em->persist($permission);
            $em->flush();

            $formRoles = $request->request->get('form_role', '');
            $roles = $this->getDoctrine()
                ->getRepository(Role::class)
                ->findAll();

            foreach($roles as $role) {

                if (!empty($formRoles) && in_array($role->getId(), $formRoles)) {
                    $role->addPermission($permission);
                    $em->persist($role);
                } else {
                    $role->removePermission($permission);
                    $em->persist($role);
                }
            }
            $em->flush();

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
            ->findOneBy(array('page' => $page));

        $roles = $this->getDoctrine()
            ->getRepository(Role::class)
            ->findAll();

        $locales = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->findAll();

        if ($permission) {
            $setRoles = $this->getDoctrine()
                ->getRepository(Role::class)
                ->findByRolesByPermissionId($permission->getId());
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
    * @Route("/api/v1/page/delete/", name="api_page_delete", methods={"PUT"})
    * @Route("/api/v1/page/delete/{id}/", name="api_page_delete_multiple", methods={"DELETE"})
    */
    final public function delete(int $id=0)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $pageId) {

                $em = $this->getDoctrine()->getManager();
                $page = $em->getRepository(PageContent::class)->find($pageId);

                if ($page) {
                    $page = $page->getPage();

                    $em->remove($page);
                    $em->flush();
                }
            }

            $response = ['success' => true];

        } else {
            $response = [
                'success' => false,
                'message' => 'Id cannot be empty',
            ];
        }

        return $this->json($response);
    }

    /**
    * @Route("/api/v1/page/{id}/{_locale}/", name="api_page_content"), methods={"GET","HEAD"})
    */
    final public function getPageContent(int $id, $_locale, Request $request)
    {
        $locale = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->findOneBy(['locale' => $_locale]);

        if (!empty($id)) {

            $page = $this->getDoctrine()
                ->getRepository(PageContent::class)
                ->findOneBy(['page' => $id, 'locale' => $locale]);

            if ($page) {
                $response = [
                    'success' => true,
                    'data' => $page,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find page',
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Id cannot be empty',
            ];
        }

        return $this->json($response);
    }
}
