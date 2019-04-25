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

use App\Entity\Role;
use App\Entity\Permission;
use App\Entity\PermissionGroup;
use App\Entity\RolePermission;
use App\Service\LogService;


class RoleController extends Controller
{
    /**
    * @Route("/api/v1/user/role/list/", name="api_role_list"), methods={"GET","HEAD"})
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

        $qb = $this->getDoctrine()->getRepository(Role::class)->createQueryBuilder('l');
        $qb->select('count(l.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->getDoctrine()->getRepository(Role::class)->createQueryBuilder('l');
        $qb->select();
        $qb->where($whereString);
        $qb->orderBy('l.'.$sort_column, $sort_direction);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $roles = $qb->getQuery()->getResult();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $json = array(
            'total' => $count,
            'data' => $roles,
        );

        $json = $serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/user/role/get/{id}/", name="api_user_role_get"), methods={"GET","HEAD"})
    */
    final public function getRole($id, Request $request)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        if (!empty($id)) {
            $role = $this->getDoctrine()
            ->getRepository(Role::class)
            ->find($id);
            if ($role) {
                $response = [
                    'success' => true,
                    'data' => $role,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find role',
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
    * @Route("/api/v1/user/role/fields/", name="api_user_role_fields"), methods={"GET","HEAD"})
    */
    final public function getRoleFields(Request $request, TranslatorInterface $translator)
    {
        $fields = array(
            [
                'id' => 'name',
                'type' => 'text',
                'label' => $translator->trans('Description'),
                'editable' => true,
                'required' => true,
                ],
                [
                    'id' => 'description',
                    'type' => 'text',
                    'label' => $translator->trans('Description'),
                    'editable' => true,
                    'required' => false,
                    ],
                    [
                        'id' => 'active',
                        'type' => 'checkbox',
                        'label' => $translator->trans('Active'),
                        'editable' => true,
                        'required' => false,
                        ],
                    );

                    $permissionGroups = $this->getDoctrine()
                    ->getRepository(PermissionGroup::class)
                    ->findAll();

                    if ($permissionGroups)
                    foreach($permissionGroups as $permissionGroup) {

                        $permissions = $this->getDoctrine()
                        ->getRepository(Permission::class)
                        ->findBy(['permissionGroup' => $permissionGroup->getId()]);

                        $options = array();
                        if ($permissions)
                        foreach($permissions as $permission) {
                            $options[$permission->getId()] = $permission->getDescription();
                        }

                        $fields[] = [
                            'id' => 'permissions',
                            'label' => $permissionGroup->getName(),
                            'type' => 'checkboxes',
                            'options' => $options,
                            'required' => false,
                            'editable' => true,
                        ];
                    }

                    $response = [
                        'success' => true,
                        'fields' => $fields,
                    ];

                    $json = json_encode($response);
                    return $this->json($json);
                }

                /**
                * @Route("/api/v1/user/role/insert/", name="api_user_role_insert", methods={"PUT"})
                * @Route("/api/v1/user/role/update/{id}/", name="api_user_role_update", methods={"PUT"})
                */
                final public function edit($id=0, Request $request, TranslatorInterface $translator, LogService $log)
                {
                    $encoders = array(new XmlEncoder(), new JsonEncoder());
                    $normalizers = array(new ObjectNormalizer());
                    $serializer = new Serializer($normalizers, $encoders);
                    $logMessage = '';
                    $logComment = 'Insert';

                    if (!empty($id)) {
                        $role = $this->getDoctrine()
                        ->getRepository(Role::class)
                        ->find($id);
                        if (!$role) {
                            $response = [
                                'success' => false,
                                'message' => 'Cannot find role',
                            ];
                            $json = json_encode($response);
                            return $this->json($json);

                        } else {
                            $logMessage .= '<i>Old data:</i><br>';
                            $logMessage .= $serializer->serialize($role, 'json');
                            $logMessage .= '<br><br>';
                            $logComment = 'Update';
                            $message = 'Role has been updated';

                        }
                    } else {
                        $role = new Role();
                        $message = 'Role has been inserted';
                    }

                    if ($request->isMethod('PUT')) {

                        $params = json_decode(file_get_contents("php://input"),true);

                        if (!empty($params['name'])) $role->setName($params['name']);
                        else $errors[] = 'Name cannot be empty';

                        if (isset($params['description'])) $role->setDescription($params['description']);

                        if (!empty($params['active'])) $role->setActive(true);
                        else $role->setActive(false);

                        $permissions = $this->getDoctrine()
                        ->getRepository(Permission::class)
                        ->findAll();

                        if ($permissions)
                        foreach($permissions as $permission) {
                            if (!empty($params['permissions-' . $permission->getId()])) {
                                $role->addPermission($permission);
                            } else {
                                $role->removePermission($permission);
                            }
                        }

                        if (!empty($errors)) {

                            $response = [
                                'success' => false,
                                'message' => $errors,
                            ];
                            $json = json_encode($response);
                            return $this->json($json);
                        }

                        $logMessage .= '<i>New data:</i><br>';
                        $logMessage .= $serializer->serialize($role, 'json');

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($role);
                        $em->flush();
                        $id = $role->getId();

                        $log->add('Role', $id, $logMessage, $logComment);

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
                * @Route("/api/v1/user/role/delete/", name="api_user_role_delete", methods={"PUT"})
                * @Route("/api/v1/user/role/delete/{id}/", name="api_user_role_delete_multiple", methods={"DELETE"})
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
                        foreach($toRemove as $cronId) {

                            $em = $this->getDoctrine()->getManager();
                            $role = $em->getRepository(Role::class)->find($cronId);

                            if ($role) {
                                $logMessage = '<i>Data:</i><br>';
                                $logMessage .= $serializer->serialize($role, 'json');

                                $log->add('Role', $id, $logMessage, 'Delete');

                                $em->remove($role);
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
