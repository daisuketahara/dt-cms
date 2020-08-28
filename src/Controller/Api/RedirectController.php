<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Yaml\Yaml;

use App\Entity\Redirect;

class RedirectController extends AbstractController
{
    /**
    * @Route("/api/v1/redirect/info/", name="api_redirect_info"), methods={"GET","HEAD"})
    */
    final public function info(Request $request, TranslatorInterface $translator)
    {
        $properties = Yaml::parseFile('src/Config/redirect.yaml');

        $api = [];
        $settings = ['title' => 'redirects'];

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

        $json = array(
            'total' => $count,
            'data' => $redirects,
        );

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/redirect/get/{id}/", name="api_redirect_get"), methods={"GET","HEAD"})
    */
    final public function getRedirect(int $id, Request $request)
    {
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

        return $this->json($response);
    }

    /**
    * @Route("/api/v1/redirect/insert/", name="api_redirect_insert", methods={"PUT"})
    * @Route("/api/v1/redirect/update/{id}/", name="api_redirect_update", methods={"PUT"})
    */
    final public function edit(int $id=0, Request $request, TranslatorInterface $translator)
    {
        if (!empty($id)) {
            $redirect = $this->getDoctrine()
                ->getRepository(Redirect::class)
                ->find($id);

            if (!$redirect) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find redirect',
                ];
                return $this->json($response);

            } else {
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
                return $this->json($response);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($redirect);
            $em->flush();
            $id = $redirect->getId();

            $response = [
                'success' => true,
                'id' => $id,
                'message' => $message,
            ];
        }

        return $this->json($response);
    }

    /**
    * @Route("/api/v1/redirect/delete/", name="api_redirect_delete", methods={"PUT"})
    * @Route("/api/v1/redirect/delete/{id}/", name="api_redirect_delete_multiple", methods={"DELETE"})
    */
    final public function delete(int $id=0)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $redirectId) {

                $em = $this->getDoctrine()->getManager();
                $redirect = $em->getRepository(Redirect::class)->find($redirectId);

                if ($redirect) {
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

        return $this->json($response);
    }
}
