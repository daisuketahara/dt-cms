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
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use App\Entity\Page;
use App\Service\LogService;
use App\Service\RedirectService;

class PageController extends Controller
{
    public function loadPage(Request $request, RedirectService $redirect)
    {
        $route = $request->attributes->get('_route');

        $page = $this->getDoctrine()
            ->getRepository(Page::class)
            ->findByRoute($route);

        if (!$page) {

            $newRoute = $redirect->getRedirect($route);
            if ($newRoute) return $this->redirect($newRoute->getNewPageRoute(), $newRoute->getRedirectType());

            return $this->render('page/404.html.twig');
        }

        return $this->render('page/page.html.twig', array(
            'page_title' => $page->getPageTitle(),
            'content' => $page->getContent(),
            'meta_title' => $page->getMetaTitle(),
            'meta_keywords' => $page->getMetaKeywords(),
            'meta_description' => $page->getMetaDescription(),
            'meta_custom' => $page->getMetaCustom(),
            'publish_date' => $page->getPublishDate(),
            'page_width' => $page->getPageWidth(),
            'disable_layout' => $page->getDisableLayout(),
            'main_image' => $page->getMainImage(),
            'custom_css' => $page->getCustomCss(),
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
      */
    final public function edit($id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $page = $this->getDoctrine()
                ->getRepository(Page::class)
                ->find($id);

            if (!$page) {
                $page = new Page();
                $this->addFlash(
                    'error',
                    $translator->trans('The requested page does not exist!')
                );
            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $serializer->serialize($page, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';
            }
        } else {
            $page = new Page();
        }

        $form = $this->createFormBuilder();
        $form = $form->getForm();
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {

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
            $page->setPageWidth($request->request->get('page-width', ''));
            $page->setDisableLayout($request->request->get('disable-layout', 0));

            $page->setCustomCss($request->request->get('custom-css', ''));
            $page->setCustomJs($request->request->get('custom-js', ''));

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($page, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();
            $id = $page->getId();

            $log->add('Page', $id, $logMessage, $logComment);

            $this->addFlash(
                'success',
                $translator->trans('Your changes were saved!')
            );
            return $this->redirectToRoute('page_edit', array('id' => $id));
        }

        // https://symfony.com/doc/current/security/csrf.html
        //if ($this->isCsrfTokenValid('edit-page', $submittedToken)) {
        // ... do something, like deleting an object
        //}



        if (!empty($id)) $title = $translator->trans('Edit page');
        else $title = $translator->trans('Add page');

        return $this->render('page/admin/edit.html.twig', array(
            'page_title' => $title,
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
            'locale' => '',
            'page_width' => $page->getPageWidth(),
            'disable_layout' => $page->getDisableLayout(),
            'authorization' => '',
            'main_image' => $page->getMainImage(),
            'custom_css' => $page->getCustomCss(),
            'custom_js' => $page->getCustomJs(),
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
}
