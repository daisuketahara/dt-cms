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

use App\Entity\Redirect;
use App\Form\RedirectForm;
use App\Service\LogService;


class RedirectController extends Controller
{
    /**
     * @Route("/{_locale}/admin/redirect", name="redirect"))
     */
     final public function list(TranslatorInterface $translator)
     {
         return $this->render('redirect/admin/list.html.twig', array(
             'page_title' => $translator->trans('Redirects'),
             'can_add' => true,
             'can_edit' => true,
             'can_delete' => true,
         ));
     }

     /**
      * @Route("/{_locale}/admin/redirect/ajaxlist", name="redirect_ajaxlist"))
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

         $qb = $this->getDoctrine()->getRepository(Redirect::class)->createQueryBuilder('s');
         $qb->select('count(s.id)');
         $qb->where($whereString);
         $count = $qb->getQuery()->getSingleScalarResult();

         if (empty($limit)) {
             $redirects = $this->getDoctrine()
                 ->getRepository(Redirect::class)
                 ->findBy($where, array($sort_column => $sort_direction));
         } else {
             $redirects = $this->getDoctrine()
                 ->getRepository(Redirect::class)
                 ->findBy($where, array($sort_column => $sort_direction), $limit, $offset);
         }

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);

         $json = array(
             'total' => 6,
             'data' => $redirects
         );

         $json = $serializer->serialize($json, 'json');

         return $this->json($json);
     }

     /**
      * @Route("/{_locale}/admin/redirect/add", name="redirect_add"))
      * @Route("/{_locale}/admin/redirect/edit/{id}", name="redirect_edit"))
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
            $redirect = $this->getDoctrine()
                ->getRepository(Redirect::class)
                ->find($id);
            if (!$redirect) {
                $redirect = new Redirect();
                $this->addFlash(
                    'error',
                    $translator->trans('The requested redirect does not exist!')
                );
            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $serializer->serialize($redirect, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';

            }
        } else {
            $redirect = new Redirect();
        }

        $form = $this->createForm(RedirectForm::class, $redirect);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // perform some action...
            $redirect = $form->getData();

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($redirect, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($redirect);
            $em->flush();
            $id = $redirect->getId();

            $log->add('Redirect', $id, $logMessage, $logComment);

            $this->addFlash(
                'success',
                $translator->trans('Your changes were saved!')
            );
            return $this->redirectToRoute('redirect_edit', array('id' => $id));
        }

        if (!empty($id)) $title = $translator->trans('Edit redirect');
        else $title = $translator->trans('Add redirect');

        return $this->render('common/form.html.twig', array(
            'form' => $form->createView(),
            'page_title' => $title,
        ));
     }

     /**
      * @Route("/{_locale}/admin/redirect/delete/{id}", name="redirect_delete"))
      */
     final public function delete($id, LogService $log)
     {
         $em = $this->getDoctrine()->getManager();
         $redirect = $em->getRepository(Redirect::class)->find($id);

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);
         $logMessage = '<i>Data:</i><br>';
         $logMessage .= $serializer->serialize($redirect, 'json');

         $log->add('Redirect', $id, $logMessage, 'Delete');

         $em->remove($redirect);
         $em->flush();

         return new Response(
             '1'
         );
     }
}
