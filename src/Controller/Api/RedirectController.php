<?php

namespace App\Controller\Api;

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
use App\Service\LogService;

class RedirectController extends Controller
{
    /**
    * @Route("/api/v1/redirect/info/", name="api_redirect_info"), methods={"GET","HEAD"})
    */
    final public function info(Request $request, TranslatorInterface $translator)
    {
        $info = array(
            'api' => array(
                'list' => '/redirect/list/',
                'get' => '/redirect/get/',
                'insert' => '/redirect/insert/',
                'update' => '/redirect/update/',
                'delete' => '/redirect/delete/'
            ),
            'fields' => array(
                [
                    'id' => 'id',
                    'label' => 'id',
                    'type' => 'integer',
                    'required' => true,
                    'editable' => false,
                    'show_list' => true,
                    'show_form' => false,
                ],
                [
                    'id' => 'oldPageRoute',
                    'label' => 'old_page_route',
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => true,
                    'show_form' => true,
                ],
                [
                    'id' => 'newPageRoute',
                    'label' => 'new_page_route',
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => false,
                    'show_form' => true,
                ],
                [
                    'id' => 'redirectType',
                    'label' => 'redirect_type',
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => false,
                    'show_form' => true,
                ],
                [
                    'id' => 'active',
                    'label' => 'active',
                    'type' => 'checkbox',
                    'required' => false,
                    'editable' => true,
                    'show_list' => true,
                    'show_form' => true,
                ]
            ),
        );
        return $this->json(json_encode($info));
    }

    /**
    * @Route("/api/v1/redirect/list/", name="api_redirect_list"), methods={"GET","HEAD"})
    */
    final public function list(Request $request)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['sort'])) $sort_column = $params['sort']; else $sort_column = 'id';
        if (!empty($params['dir'])) $sort_direction = $params['dir']; else $sort_direction = 'desc';
        if (!empty($params['limit'])) $limit = $params['limit']; else $limit = 15;
        if (!empty($params['offset'])) $offset = $params['offset']; else $offset = 0;
        if (!empty($params['filter'])) $filter = $params['filter'];

        $whereString = '1=1';
        if (!empty($filter)) {
            parse_str($filter, $filter_array);
            foreach($filter_array as $key => $value) {
                if (!empty($value)) {
                    //$where[$key] = $value;
                    $whereString .= " AND l." . $key . " LIKE '%" . $value . "%'";
                }
            }
        }

        $qb = $this->getDoctrine()->getRepository(Redirect::class)->createQueryBuilder('l');
        $qb->select('count(l.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->getDoctrine()->getRepository(Redirect::class)->createQueryBuilder('l');
        $qb->select();
        $qb->where($whereString);
        $qb->orderBy('l.'.$sort_column, $sort_direction);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $redirects = $qb->getQuery()->getResult();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $json = array(
            'total' => $count,
            'data' => $redirects,
        );

        $json = $serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/redirect/get/{id}/", name="api_redirect_get"), methods={"GET","HEAD"})
    */
    final public function getRedirect($id, Request $request)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        if (!empty($id)) {
            $redirect = $this->getDoctrine()
            ->getRepository(Redirect::class)
            ->find($id);
            if ($redirect) {
                $response = [
                    'success' => true,
                    'data' => $redirect,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find redirect',
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Id cannot be empty',
            ];
        }

        $json = $serializer->serialize($response, 'json');
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/redirect/insert/", name="api_redirect_insert", methods={"PUT"})
    * @Route("/api/v1/redirect/update/{id}/", name="api_redirect_update", methods={"PUT"})
    */
    final public function edit($id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
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
                $response = [
                    'success' => false,
                    'message' => 'Cannot find redirect',
                ];
                $json = json_encode($response);
                return $this->json($json);

            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $serializer->serialize($redirect, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';
                $message = 'Redirect has been updated';

            }
        } else {
            $redirect = new Redirect();
            $message = 'Redirect has been inserted';
        }

        if ($request->isMethod('PUT')) {

            $params = json_decode(file_get_contents("php://input"),true);

            if (isset($params['oldPageRoute'])) $redirect->setOldPageRoute($params['oldPageRoute']);
            else $errors[] = 'Old page route cannot be empty';

            if (isset($params['newPageRoute'])) $redirect->setNewPageRoute($params['newPageRoute']);
            else $errors[] = 'New page route cannot be empty';

            if (isset($params['redirectType'])) $redirect->setRedirectType($params['redirectType']);
            else $errors[] = 'Redirect type cannot be empty';

            if (!empty($params['active'])) $redirect->setActive(true);
            else $redirect->setActive(false);

            if (!empty($errors)) {

                $response = [
                    'success' => false,
                    'message' => $errors,
                ];
                $json = json_encode($response);
                return $this->json($json);
            }

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($redirect, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($redirect);
            $em->flush();
            $id = $redirect->getId();

            $log->add('Redirect', $id, $logMessage, $logComment);

            $response = [
                'success' => true,
                'id' => $id,
                'message' => $message,
            ];
        }

        $json = json_encode($response);
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/redirect/delete/", name="api_redirect_delete", methods={"PUT"})
    * @Route("/api/v1/redirect/delete/{id}/", name="api_redirect_delete_multiple", methods={"DELETE"})
    */
    final public function delete($id=0, LogService $log)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $redirectId) {

                $em = $this->getDoctrine()->getManager();
                $redirect = $em->getRepository(Redirect::class)->find($redirectId);

                if ($redirect) {
                    $logMessage = '<i>Data:</i><br>';
                    $logMessage .= $serializer->serialize($redirect, 'json');

                    $log->add('Redirect', $id, $logMessage, 'Delete');

                    $em->remove($redirect);
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

        $json = json_encode($response);
        return $this->json($json);
    }
}
