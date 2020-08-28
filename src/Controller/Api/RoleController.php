<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Yaml\Yaml;

use App\Entity\Role;
use App\Entity\Permission;
use App\Entity\PermissionGroup;
use App\Entity\RolePermission;


class RoleController extends AbstractController
{
    /**
    * @Route("/api/v1/user/role/info/", name="api_user_role_info"), methods={"GET","HEAD"})
    */
    final public function info(Request $request, TranslatorInterface $translator)
    {
        $properties = Yaml::parseFile('src/Config/role.yaml');

        $api = [];
        $settings = ['title' => 'user_roles'];

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


        $permissionGroups = $this->getDoctrine()
            ->getRepository(PermissionGroup::class)
            ->findAll();

        if ($permissionGroups)
        foreach($permissionGroups as $permissionGroup) {

            $permissions = $this->getDoctrine()
                ->getRepository(Permission::class)
                ->findBy(['permissionGroup' => $permissionGroup->getId()]);

            $options = array();
            if ($permissions) {
                foreach($permissions as $permission) {
                    $label = $permission->getDescription();
                    if (empty($label)) $label = $permission->getRouteName();
                    $options[$permission->getId()] = $label;
                }

                $info['fields'][] = [
                    'id' => 'permissions',
                    'label' => $permissionGroup->getName(),
                    'type' => 'checkboxes',
                    'options' => $options,
                    'required' => false,
                    'editable' => true,
                    'list' => false,
                    'form' => true,
                ];
            }
        }

        $permissions = $this->getDoctrine()
            ->getRepository(Permission::class)
            ->findBy(['permissionGroup' => null]);

        $options = array();
        if ($permissions) {
            foreach($permissions as $permission) {
                $label = $permission->getDescription();
                if (empty($label)) $label = $permission->getRouteName();
                $options[$permission->getId()] = $label;
            }

            $info['fields'][] = [
                'id' => 'permissions',
                'label' => 'Other',
                'type' => 'checkboxes',
                'options' => $options,
                'required' => false,
                'editable' => true,
                'list' => false,
                'form' => true,
            ];
        }

        if (!empty($properties['buttons'])) $info['buttons'] = $properties['buttons'];

        return $this->json($info);
    }

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

        $json = array(
            'total' => $count,
            'data' => $roles,
        );

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/user/role/get/{id}/", name="api_user_role_get"), methods={"GET","HEAD"})
    */
    final public function getRole(int $id, Request $request)
    {
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

        return $this->json($response);
    }

    /**
    * @Route("/api/v1/user/role/insert/", name="api_user_role_insert", methods={"PUT"})
    * @Route("/api/v1/user/role/update/{id}/", name="api_user_role_update", methods={"PUT"})
    */
    final public function edit(int $id=0, Request $request, TranslatorInterface $translator)
    {
        if (!empty($id)) {
            $role = $this->getDoctrine()
                ->getRepository(Role::class)
                ->find($id);
            if (!$role) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find role',
                ];
                return $this->json($response);

            } else {
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
                return $this->json($response);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($role);
            $em->flush();
            $id = $role->getId();

            $response = [
                'success' => true,
                'id' => $id,
                'message' => $message,
            ];
        }

        return $this->json($response);
    }


    /**
    * @Route("/api/v1/user/role/delete/", name="api_user_role_delete", methods={"PUT"})
    * @Route("/api/v1/user/role/delete/{id}/", name="api_user_role_delete_multiple", methods={"DELETE"})
    */
    final public function delete(int $id=0)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $cronId) {

                $em = $this->getDoctrine()->getManager();
                $role = $em->getRepository(Role::class)->find($cronId);

                if ($role) {
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

        return $this->json($response);
    }

}
