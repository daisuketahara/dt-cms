<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use App\Entity\Log;

class LogController extends Controller
{
    /**
     * @Route("/{_locale}/admin/log", name="log_locale")
     * @Route("/admin/log", name="log")
     */
     final public function list(TranslatorInterface $translator)
     {
         return $this->render('log/admin/list.html.twig', array(
             'page_title' => $translator->trans('Log'),
             'can_add' => true,
             'can_edit' => true,
             'can_delete' => true,
         ));
     }

     /**
      * @Route("/{_locale}/admin/log/ajaxlist", name="log_ajaxlist")
      */
     final public function ajaxlist(Request $request)
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

         $qb = $this->getDoctrine()->getRepository(Log::class)->createQueryBuilder('l');
         $qb->select('count(l.id)');
         $qb->where($whereString);
         $count = $qb->getQuery()->getSingleScalarResult();

         if (!empty($limit)) {
             $log = $this->getDoctrine()
                 ->getRepository(Log::class)
                 ->findBy($where, array($sort_column => $sort_direction), $limit, $offset);
         } else {
              $log = $this->getDoctrine()
                  ->getRepository(Log::class)
                  ->findBy($where, array($sort_column => $sort_direction));
         }

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);

         $json = array(
             'total' => $count,
             'data' => $log
         );

         $json = $serializer->serialize($json, 'json');

         return $this->json($json);
     }
}
