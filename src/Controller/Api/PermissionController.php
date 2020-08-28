<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Yaml\Yaml;

use App\Entity\Permission;
use App\Entity\PermissionGroup;

class PermissionController extends AbstractController
{
    /**
    * @Route("/api/v1/permission/info/", name="api_permission_info"), methods={"GET","HEAD"})
    */
    final public function info(Request $request, TranslatorInterface $translator)
    {
        $properties = Yaml::parseFile('src/Config/permission.yaml');

        $api = [];
        $settings = ['title' => 'permissions'];

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
    * @Route("/api/v1/permission/list/", name="api_permission_list"), methods={"GET","HEAD"})
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

        $qb = $this->getDoctrine()->getRepository(Permission::class)->createQueryBuilder('l');
        $qb->select('count(l.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->getDoctrine()->getRepository(Permission::class)->createQueryBuilder('l');
        $qb->select();
        $qb->where($whereString);
        $qb->orderBy('l.'.$sort_column, $sort_direction);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $permissions = $qb->getQuery()->getResult();

        $json = array(
            'total' => $count,
            'data' => $permissions,
        );

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/permission/get/{id}/", name="api_permission_get"), methods={"GET","HEAD"})
    */
    final public function getPermission(int $id, Request $request)
    {
        if (!empty($id)) {
            $permission = $this->getDoctrine()
            ->getRepository(Permission::class)
            ->find($id);
            if ($permission) {
                $response = [
                    'success' => true,
                    'data' => $permission,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find permission',
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
    * @Route("/api/v1/permission/insert/", name="api_permission_insert", methods={"PUT"})
    * @Route("/api/v1/permission/update/{id}/", name="api_permission_update", methods={"PUT"})
    */
    final public function edit(int $id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        if (!empty($id)) {
            $permission = $this->getDoctrine()
                ->getRepository(Permission::class)
                ->find($id);

            if (!$permission) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find permission',
                ];
                return $this->json($response);

            } else {
                $message = 'Permission has been updated';

            }
        } else {
            $permission = new Permission();
            $message = 'Permission has been inserted';
        }

        if ($request->isMethod('PUT')) {

            $params = json_decode(file_get_contents("php://input"),true);

            if (isset($params['permissionGroup'])) $permission->setPermissionGroup($params['permissionGroup']);

            if (isset($params['page'])) $permission->setPage($params['page']);

            if (isset($params['routeName'])) $permission->setRouteName($params['routeName']);
            else $errors[] = 'Route name cannot be empty';

            if (isset($params['description'])) $permission->setDescription($params['description']);
            else $errors[] = 'Description cannot be empty';

            if (!empty($errors)) {

                $response = [
                    'success' => false,
                    'message' => $errors,
                ];
                return $this->json($response);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($permission);
            $em->flush();
            $id = $permission->getId();

            $response = [
                'success' => true,
                'id' => $id,
                'message' => $message,
            ];
        }

        return $this->json($response);
    }

    /**
    * @Route("/api/v1/permission/delete/", name="api_permission_delete", methods={"PUT"})
    * @Route("/api/v1/permission/delete/{id}/", name="api_permission_delete_multiple", methods={"DELETE"})
    */
    final public function delete(int $id=0)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $permissionId) {

                $em = $this->getDoctrine()->getManager();
                $permission = $em->getRepository(Permission::class)->find($permissionId);

                if ($permission) {
                    $em->remove($permission);
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
    * @Route("/api/v1/permission/populate/", name="api_permission_populate"), methods={"GET","HEAD"})
    */
    final public function populate(TranslatorInterface $translator, KernelInterface $kernel)
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
            'command' => 'debug:router',
        ));
        $output = new BufferedOutput();
        $application->run($input, $output);

        // return the output, don't use if you used NullOutput()
        $content = $output->fetch();
        $fieldLengths = array();
        $routes = array();

        $i = 0;
        $count = 0;
        $lines = preg_split("/((\r?\n)|(\n?\r))/", $content);
        foreach($lines as $line){
            if (empty($i)) {
                $fields = explode(' ', $line);
                foreach($fields as $field) {
                    $fieldLengths[] = strlen($field);
                }
            } elseif ($i < 3) {
                $i++;
                continue;
            } elseif ($i > (count($lines)-4)) {
                $i++;
                continue;
            } else {
                $name = trim(substr($line, 1, $fieldLengths[1]));
                $path = trim(substr($line, ($fieldLengths[1]+$fieldLengths[2]+$fieldLengths[3]+$fieldLengths[4]+6), $fieldLengths[5]));

                $routes[] = array(
                    'name' => $name,
                    'path' => $path
                );
            }
            $i++;
        }

        if (!empty($routes)) {
            foreach ($routes as $key => $route) {

                // Skip if first character _, redirect_ or cron_
                $nameSplit = explode('_', $route['name']);
                if (in_array($nameSplit[0], array('', 'cron','redirect','page'))) {
                    continue;
                }

                $permission = $this->getDoctrine()
                    ->getRepository(Permission::class)
                    ->findOneBy(['routeName' => $route['name']]);

                if (!$permission) {
                    $permission = new Permission();
                }

                $path = str_replace('/{_locale}', '', $route['path']);
                $path = str_replace('{id}', ':id', $path);

                $permission->setRouteName($route['name']);
                $permission->setRoute($path);

                $em = $this->getDoctrine()->getManager();
                $em->persist($permission);
                $em->flush();

            }
        }

        $response = [
            'success' => true,
            'message'=> $translator->trans('Missing permissions scan completed'),
        ];
        return $this->json($response);
    }

    /**
    * @Route("/api/v1/permission/fields/", name="api_permission_fields"), methods={"GET","HEAD"})
    */
    final public function getPermissionFields(Request $request)
    {
        $permissionGroups = $this->getDoctrine()
            ->getRepository(PermissionGroup::class)
            ->findAll();

        $info = array();

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

                $info[] = [
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

            $info[] = [
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
        $response = [
            'success' => true,
            'data' => $info
        ];
        return $this->json($response);
    }
}
