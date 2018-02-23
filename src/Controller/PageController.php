<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use App\Entity\Page;
use App\Service\LogService;

class PageController extends Controller
{
    final public function show($slug)
    {
        // $slug will equal the dynamic part of the URL
        // e.g. at /blog/yay-routing, then $slug='yay-routing'

        // ...
    }

    final public function notfound()
    {
        throw $this->createNotFoundException('The file does not exist');
        return $this->render('page/404.html.twig');
    }

    /**
     * @Route("/{_locale}/admin/page", name="page_admin")
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
      * @Route("/{_locale}/admin/page/ajaxlist", name="page_ajaxlist")
      */
    final public function ajaxlist()
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
      * @Route("/{_locale}/admin/page/add", name="page_add")
      * @Route("/{_locale}/admin/page/edit/{id}", name="page_edit")
      */
    final public function edit($id)
    {
        if (!empty($id)) {
            $page = $this->getDoctrine()
                ->getRepository(Page::class)
                ->find($id);

            if (!$page) {
                throw $this->createNotFoundException(
                    'Page not found, page id: '.$id
                );
            }
        }

        // https://symfony.com/doc/current/security/csrf.html
        //if ($this->isCsrfTokenValid('edit-page', $submittedToken)) {
        // ... do something, like deleting an object
        //}

        return $this->render('page/admin/edit.html.twig', array(
            'page' => array(
            ),
                'page_title' => $page->getPageTitle(),
                'slug' => $page->getSlug(),
                'content' => $page->getContent(),
                'meta_title' => $page->getMetaTitle(),
                'meta_keywords' => $page->getMetaKeywords(),
                'meta_description' => $page->getMetaDescription(),
                'meta_custom' => $page->getMetaCustom(),
                'publish_date' => '',
                'expire_date' => '',
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
      * @Route("/{_locale}/admin/page/delete/{id}", name="page_delete")
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
