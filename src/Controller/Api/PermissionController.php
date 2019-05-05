<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use App\Entity\Permission;
use App\Service\LogService;

class PermissionController extends Controller
{
    /**
    * @Route("/api/v1/permission/info/", name="api_permission_info"), methods={"GET","HEAD"})
    */
    final public function info(Request $request, TranslatorInterface $translator)
    {
        $info = array(
            'api' => array(
                'list' => '/permission/list/',
                'get' => '/permission/get/',
                'insert' => '/permission/insert/',
                'update' => '/permission/update/',
                'delete' => '/permission/delete/'
            ),
            'buttons' => array(
                [
                    'id' => 'populate',
                    'label' => 'get_missing_permissions',
                    'api' => '/permission/populate/'
                ]
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
                    'id' => 'permissionGroup',
                    'label' => 'group',
                    'type' => 'text',
                    'required' => false,
                    'editable' => false,
                    'show_list' => false,
                    'show_form' => false,
                ],
                [
                    'id' => 'page',
                    'label' => 'page',
                    'type' => 'text',
                    'required' => false,
                    'editable' => false,
                    'show_list' => false,
                    'show_form' => true,
                ],
                [
                    'id' => 'routeName',
                    'label' => 'route_name',
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => true,
                    'show_form' => true,
                ],
                [
                    'id' => 'description',
                    'label' => 'description',
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => true,
                    'show_form' => true,
                ],
                [
                    'id' => 'component',
                    'label' => 'component',
                    'type' => 'text',
                    'required' => false,
                    'editable' => true,
                    'show_list' => false,
                    'show_form' => true,
                ],
                [
                    'id' => 'props',
                    'label' => 'props',
                    'type' => 'text',
                    'required' => false,
                    'editable' => true,
                    'show_list' => false,
                    'show_form' => true,
                ]
            ),
        );
        return $this->json(json_encode($info));
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

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $json = array(
            'total' => $count,
            'data' => $permissions,
        );

        $json = $serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/permission/get/{id}/", name="api_permission_get"), methods={"GET","HEAD"})
    */
    final public function getPermission($id, Request $request)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

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

        $json = $serializer->serialize($response, 'json');
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/permission/insert/", name="api_permission_insert", methods={"PUT"})
    * @Route("/api/v1/permission/update/{id}/", name="api_permission_update", methods={"PUT"})
    */
    final public function edit($id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $permission = $this->getDoctrine()
            ->getRepository(Permission::class)
            ->find($id);
            if (!$permission) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find permission',
                ];
                $json = json_encode($response);
                return $this->json($json);

            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $serializer->serialize($permission, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';
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
                $json = json_encode($response);
                return $this->json($json);
            }

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($permission, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($permission);
            $em->flush();
            $id = $permission->getId();

            $log->add('Permission', $id, $logMessage, $logComment);

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
    * @Route("/api/v1/permission/delete/", name="api_permission_delete", methods={"PUT"})
    * @Route("/api/v1/permission/delete/{id}/", name="api_permission_delete_multiple", methods={"DELETE"})
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
            foreach($toRemove as $permissionId) {

                $em = $this->getDoctrine()->getManager();
                $permission = $em->getRepository(Permission::class)->find($permissionId);

                if ($permission) {
                    $logMessage = '<i>Data:</i><br>';
                    $logMessage .= $serializer->serialize($permission, 'json');

                    $log->add('Permission', $id, $logMessage, 'Delete');

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

        $json = json_encode($response);
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/permission/populate/", name="api_permission_populate"), methods={"GET","HEAD"})
    */
    final public function populate(TranslatorInterface $translator, LogService $log, KernelInterface $kernel) {

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

                $permission->setRouteName($route['name']);
                $permission->setRoute($path);

                $em = $this->getDoctrine()->getManager();
                $em->persist($permission);
                $em->flush();

            }
        }

        $log->add('Permission', 0, '<i>Permissions table populated:</i><br>', 'Permission populate');
        $response = [
            'success' => true,
            'message'=> $translator->trans('Missing permissions scan completed'),
        ];
        $json = json_encode($response);
        return $this->json($json);
    }
}
