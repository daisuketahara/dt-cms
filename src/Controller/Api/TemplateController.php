<?php

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Leafo\ScssPhp\Compiler;

use App\Entity\Template;
use App\Service\LogService;

class TemplateController extends Controller
{

    /**
    * @Route("/api/v1/template/list/", name="api_template_list"), methods={"GET","HEAD"})
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

        $qb = $this->getDoctrine()->getRepository(Template::class)->createQueryBuilder('l');
        $qb->select('count(l.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->getDoctrine()->getRepository(Template::class)->createQueryBuilder('l');
        $qb->select();
        $qb->where($whereString);
        $qb->orderBy('l.'.$sort_column, $sort_direction);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $templates = $qb->getQuery()->getResult();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $json = array(
            'total' => $count,
            'data' => $templates,
        );

        $json = $serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/template/get/{id}/", name="api_template_get"), methods={"GET","HEAD"})
    */
    final public function getTemplate($id, Request $request)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        if (!empty($id)) {
            $template = $this->getDoctrine()
            ->getRepository(Template::class)
            ->find($id);
            if ($template) {
                $response = [
                    'success' => true,
                    'data' => $template,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find template',
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
    * @Route("/api/v1/template/insert/", name="api_template_insert", methods={"PUT"})
    * @Route("/api/v1/template/update/{id}/", name="api_template_update", methods={"PUT"})
    */
    final public function edit($id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $template = $this->getDoctrine()
            ->getRepository(Template::class)
            ->find($id);
            if (!$template) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find template',
                ];
                $json = json_encode($response);
                return $this->json($json);

            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $serializer->serialize($template, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';
                $message = 'Template has been updated';

            }
        } else {
            $template = new Template();
            $message = 'Template has been inserted';
        }

        if ($request->isMethod('PUT')) {

            $params = json_decode(file_get_contents("php://input"),true);

            if (isset($params['name'])) $template->setName($params['name']);
            else $errors[] = 'Name cannot be empty';

            if (isset($params['script'])) $template->setScript($params['script']);
            else $errors[] = 'Script cannot be empty';

            if (isset($params['minute'])) $template->setMinute($params['minute']);
            else $errors[] = 'Minute cannot be empty';

            if (isset($params['hour'])) $template->setHour($params['hour']);
            else $errors[] = 'Hour cannot be empty';

            if (isset($params['day'])) $template->setDay($params['day']);
            else $errors[] = 'Day cannot be empty';

            if (isset($params['month'])) $template->setMonth($params['month']);
            else $errors[] = 'Month cannot be empty';

            if (isset($params['day_of_week'])) $template->setDayOfWeek($params['day_of_week']);
            else $errors[] = 'Day of week cannot be empty';

            if (!empty($params['active'])) $template->setActive(true);
            else $template->setActive(false);

            if (!empty($errors)) {

                $response = [
                    'success' => false,
                    'message' => $errors,
                ];
                $json = json_encode($response);
                return $this->json($json);
            }

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($template, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($template);
            $em->flush();
            $id = $template->getId();

            $log->add('Template', $id, $logMessage, $logComment);

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
    * @Route("/api/v1/template/delete/", name="api_template_delete", methods={"PUT"})
    * @Route("/api/v1/template/delete/{id}/", name="api_template_delete_multiple", methods={"DELETE"})
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
            foreach($toRemove as $templateId) {

                $em = $this->getDoctrine()->getManager();
                $template = $em->getRepository(Template::class)->find($templateId);

                if ($template) {
                    $logMessage = '<i>Data:</i><br>';
                    $logMessage .= $serializer->serialize($template, 'json');

                    $log->add('Template', $id, $logMessage, 'Delete');

                    $em->remove($template);
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
