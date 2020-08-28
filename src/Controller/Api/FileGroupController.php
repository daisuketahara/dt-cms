<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

use App\Entity\FileGroup;

class FileGroupController extends AbstractController
{
    /**
    * @Route("/api/v1/filegroup/info/", name="api_filegroup_info"), methods={"GET","HEAD"})
    */
    final public function info(Request $request, TranslatorInterface $translator)
    {
        $info = array(
            'api' => array(
                'list' => '/filegroup/list/',
                'get' => '/filegroup/get/',
                'insert' => '/filegroup/insert/',
                'update' => '/filegroup/update/',
                'delete' => '/filegroup/delete/'
            ),
            'fields' => array(
                [
                    'id' => 'id',
                    'label' => 'id',
                    'type' => 'integer',
                    'required' => true,
                    'editable' => false,
                    'list' => true,
                    'form' => false,
                ],
                [
                    'id' => 'name',
                    'label' => 'name',
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'list' => true,
                    'form' => true,
                ]
            ),
        );
        return $this->json($info);
    }

    /**
    * @Route("/api/v1/filegroup/list/", name="api_filegroup_list"), methods={"GET","HEAD"})
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

        $qb = $this->getDoctrine()->getRepository(FileGroup::class)->createQueryBuilder('l');
        $qb->select('count(l.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->getDoctrine()->getRepository(FileGroup::class)->createQueryBuilder('l');
        $qb->select();
        $qb->where($whereString);
        $qb->orderBy('l.'.$sort_column, $sort_direction);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $filegroups = $qb->getQuery()->getResult();

        $json = array(
            'total' => $count,
            'data' => $filegroups,
        );

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/filegroup/get/{id}/", name="api_filegroup_get"), methods={"GET","HEAD"})
    */
    final public function getFilegroup(int $id, Request $request)
    {
        if (!empty($id)) {
            $filegroup = $this->getDoctrine()
            ->getRepository(FileGroup::class)
            ->find($id);
            if ($filegroup) {
                $response = [
                    'success' => true,
                    'data' => $filegroup,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find filegroup',
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
    * @Route("/api/v1/filegroup/insert/", name="api_filegroup_insert", methods={"PUT"})
    * @Route("/api/v1/filegroup/update/{id}/", name="api_filegroup_update", methods={"PUT"})
    */
    final public function edit(int $id=0, Request $request, TranslatorInterface $translator)
    {
        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $filegroup = $this->getDoctrine()
                ->getRepository(FileGroup::class)
                ->find($id);
            if (!$filegroup) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find filegroup',
                ];
                return $this->json($response);

            } else {
                $message = 'Filegroup has been updated';

            }
        } else {
            $filegroup = new FileGroup();
            $message = 'Filegroup has been inserted';
        }

        if ($request->isMethod('PUT')) {

            $params = json_decode(file_get_contents("php://input"),true);

            if (isset($params['name'])) $filegroup->setName($params['name']);
            else $errors[] = 'Name cannot be empty';

            if (!empty($errors)) {

                $response = [
                    'success' => false,
                    'message' => $errors,
                ];
                return $this->json($response);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($filegroup);
            $em->flush();
            $id = $filegroup->getId();

            $response = [
                'success' => true,
                'id' => $id,
                'message' => $message,
            ];
        }

        return $this->json($response);
    }

    /**
    * @Route("/api/v1/filegroup/delete/", name="api_filegroup_delete", methods={"PUT"})
    * @Route("/api/v1/filegroup/delete/{id}/", name="api_filegroup_delete_multiple", methods={"DELETE"})
    */
    final public function delete(int $id=0)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $filegroupId) {

                $em = $this->getDoctrine()->getManager();
                $filegroup = $em->getRepository(FileGroup::class)->find($filegroupId);

                if ($filegroup) {
                    $em->remove($filegroup);
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
