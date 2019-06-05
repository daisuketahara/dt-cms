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

use App\Entity\Locale;
use App\Entity\MailQueue;
use App\Entity\MailTemplate;
use App\Entity\MailTemplateContent;
use App\Service\LogService;
use App\Service\SettingService;


class MailController extends Controller
{
    private $serializer;

    public function __construct() {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
    * @Route("/api/v1/mail/queue/info/", name="api_mail_queue_info"), methods={"GET","HEAD"})
    */
    final public function infoQueue(Request $request, TranslatorInterface $translator)
    {
        $info = array(
            'api' => array(
                'list' => '/mail/queue/list/',
                'get' => '/mail/queue/get/',
                'delete' => '/mail/queue/delete/'
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
                    'id' => 'toEmail',
                    'label' => 'to',
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => true,
                    'show_form' => true,
                ],
                [
                    'id' => 'subject',
                    'label' => 'subject',
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => true,
                    'show_form' => true,
                ],
                [
                    'id' => 'creationDate',
                    'label' => 'creation_date',
                    'type' => 'datetime',
                    'required' => true,
                    'editable' => true,
                    'show_list' => false,
                    'show_form' => true,
                ],
                [
                    'id' => 'sendDate',
                    'label' => 'send_date',
                    'type' => 'datetime',
                    'required' => true,
                    'editable' => true,
                    'show_list' => false,
                    'show_form' => true,
                ],
                [
                    'id' => 'body',
                    'label' => 'body',
                    'type' => 'texteditor',
                    'required' => true,
                    'editable' => true,
                    'show_list' => false,
                    'show_form' => true,
                ],
                [
                    'id' => 'status',
                    'label' => 'status',
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => false,
                    'show_form' => true,
                ]
            ),
        );
        return $this->json(json_encode($info));
    }

    /**
    * @Route("/api/v1/mail/queue/list/", name="api_mail_queue_list"), methods={"GET","HEAD"})
    */
    final public function queueList(Request $request)
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

        $qb = $this->getDoctrine()->getRepository(MailQueue::class)->createQueryBuilder('l');
        $qb->select('count(l.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->getDoctrine()->getRepository(MailQueue::class)->createQueryBuilder('l');
        $qb->select();
        $qb->where($whereString);
        $qb->orderBy('l.'.$sort_column, $sort_direction);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $mailqueues = $qb->getQuery()->getResult();

        $json = array(
            'total' => $count,
            'data' => $mailqueues,
        );

        $json = $this->serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/mail/queue/get/{id}/", name="api_mail_queue_get"), methods={"GET","HEAD"})
    */
    final public function getMailQueue(int $id, Request $request)
    {
        if (!empty($id)) {
            $mailqueue = $this->getDoctrine()
            ->getRepository(MailQueue::class)
            ->find($id);
            if ($mailqueue) {
                $response = [
                    'success' => true,
                    'data' => $mailqueue,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find mailqueue',
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Id cannot be empty',
            ];
        }

        $json = $this->serializer->serialize($response, 'json');
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/mail/queue/delete/", name="api_mail_queue_delete", methods={"PUT"})
    * @Route("/api/v1/mail/queue/delete/{id}/", name="api_mail_queue_delete_multiple", methods={"DELETE"})
    */
    final public function deleteFromQueue(int $id=0, LogService $log)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $mailqueueId) {

                $em = $this->getDoctrine()->getManager();
                $mailqueue = $em->getRepository(MailQueue::class)->find($mailqueueId);

                if ($mailqueue) {
                    $logMessage = '<i>Data:</i><br>';
                    $logMessage .= $this->serializer->serialize($mailqueue, 'json');

                    $log->add('MailQueue', $id, $logMessage, 'Delete');

                    $em->remove($mailqueue);
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
    * @Route("/api/v1/mail/template/info/", name="api_mail_template_info"), methods={"GET","HEAD"})
    */
    final public function infoTemplate(Request $request, TranslatorInterface $translator)
    {
        $info = array(
            'settings' => array(
                'translate' => true,
            ),
            'api' => array(
                'list' => '/mail/template/list/',
                'get' => '/mail/template/get/',
                'insert' => '/mail/template/insert/',
                'update' => '/mail/template/update/',
                'delete' => '/mail/template/delete/'
            ),
            'fields' => array(
                [
                    'object' => 'mailTemplate',
                    'id' => 'id',
                    'label' => $translator->trans('ID'),
                    'type' => 'integer',
                    'required' => true,
                    'editable' => false,
                    'show_list' => true,
                    'show_form' => false,
                ],
                [
                    'object' => 'mailTemplate',
                    'id' => 'tag',
                    'label' => $translator->trans('Tag'),
                    'type' => 'text',
                    'required' => false,
                    'editable' => false,
                    'show_list' => true,
                    'show_form' => true,
                ],
                [
                    'id' => 'name',
                    'label' => $translator->trans('Name'),
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => true,
                    'show_form' => true,
                ],
                [
                    'id' => 'subject',
                    'label' => $translator->trans('Subject'),
                    'type' => 'text',
                    'required' => true,
                    'editable' => true,
                    'show_list' => true,
                    'show_form' => true,
                ],
                [
                    'id' => 'body',
                    'label' => $translator->trans('Body'),
                    'type' => 'texteditor',
                    'required' => true,
                    'editable' => true,
                    'show_list' => false,
                    'show_form' => true,
                ]
            ),
        );
        return $this->json(json_encode($info));
    }

    /**
    * @Route("/api/v1/mail/template/list/", name="api_mail_template_list"), methods={"GET","HEAD"})
    */
    final public function templateList(Request $request)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['sort'])) $sort_column = $params['sort']; else $sort_column = 'id';
        if (!empty($params['dir'])) $sort_direction = $params['dir']; else $sort_direction = 'desc';
        if (!empty($params['limit'])) $limit = $params['limit']; else $limit = 15;
        if (!empty($params['offset'])) $offset = $params['offset']; else $offset = 0;
        if (!empty($params['filter'])) $filter = $params['filter'];
        if (!empty($params['locale'])) $localeId = $params['locale'];

        $locale = $this->getDoctrine()
        ->getRepository(Locale::class)
        ->find($localeId);

        $whereString = 'l.locale='. $locale->getId();
        if (!empty($filter)) {
            parse_str($filter, $filter_array);
            foreach($filter_array as $key => $value) {
                if (!empty($value)) {
                    //$where[$key] = $value;
                    $whereString .= " AND l." . $key . " LIKE '%" . $value . "%'";
                }
            }
        }

        $qb = $this->getDoctrine()->getRepository(MailTemplateContent::class)->createQueryBuilder('l');
        $qb->select('count(l.id)');
        $qb->where($whereString);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->getDoctrine()->getRepository(MailTemplateContent::class)->createQueryBuilder('l');
        $qb->select();
        $qb->where($whereString);
        $qb->orderBy('l.'.$sort_column, $sort_direction);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $mailTemplates = $qb->getQuery()->getResult();

        $json = array(
            'total' => $count,
            'data' => $mailTemplates,
        );

        $json = $this->serializer->serialize($json, 'json');

        return $this->json($json);
    }

    /**
    * @Route("/api/v1/mail/template/get/{id}/", name="api_mail_template_get"), methods={"GET","HEAD"})
    */
    final public function getTemplate(int $id, Request $request)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['locale'])) {
            $localeId = $params['locale'];
            $locale = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->find($localeId);
        }

        if (!empty($id)) {

            $mailTemplate = $this->getDoctrine()
            ->getRepository(MailTemplateContent::class)
            ->find($id);

            if (!empty($localeId) && $localeId != $mailTemplate->getLocale()->getId()) {
                $otherMailTemplate = $this->getDoctrine()
                ->getRepository(MailTemplateContent::class)
                ->findOneBy(['locale' => $locale, 'mailTemplate' => $mailTemplate->getMailTemplate()]);
                if ($otherMailTemplate) $mailTemplate = $otherMailTemplate;
                else $mailTemplate = true;
            }

            if ($mailTemplate) {
                $response = [
                    'success' => true,
                    'data' => $mailTemplate,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find mailTemplate',
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Id cannot be empty',
            ];
        }

        $json = $this->serializer->serialize($response, 'json');
        return $this->json($json);
    }

    /**
    * @Route("/api/v1/mail/template/insert/", name="api_mail_template_insert", methods={"PUT"})
    * @Route("/api/v1/mail/template/update/{id}/", name="api_mail_template_update", methods={"PUT"})
    */
    final public function editTemplate(int $id=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $logMessage = '';
        $logComment = 'Insert';

        $params = json_decode(file_get_contents("php://input"),true);

        if (!empty($params['locale'])) {
            $localeId = $params['locale'];
            $locale = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->find($localeId);
        }

        if (empty($locale)) {
            $locale = $this->getDoctrine()
            ->getRepository(Locale::class)
            ->getDefaultLocale();
        }

        if (!empty($id)) {

            $mailTemplate = $this->getDoctrine()
            ->getRepository(MailTemplateContent::class)
            ->find($id);

            if (!empty($mailTemplate) && !empty($localeId) && $localeId != $mailTemplate->getLocale()->getId()) {
                $otherMailTemplate = $this->getDoctrine()
                ->getRepository(MailTemplateContent::class)
                ->findOneBy(['locale' => $locale, 'mailTemplate' => $mailTemplate->getMailTemplate()]);
                if (!$otherMailTemplate) {
                    $otherMailTemplate = new MailTemplateContent();
                    $otherMailTemplate->setMailTemplate($mailTemplate->getMailTemplate());
                    $otherMailTemplate->setLocale($locale);
                }
                $mailTemplate = $otherMailTemplate;
            }

            if (!$mailTemplate) {
                $response = [
                    'success' => false,
                    'message' => 'Cannot find mailTemplate',
                ];
                $json = json_encode($response);
                return $this->json($json);

            } else {

                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $this->serializer->serialize($mailTemplate, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';
                $message = 'MailTemplate has been updated';

            }
        }

        if ($request->isMethod('PUT')) {

            if (empty($mailTemplate)) {
                $mailTemplate = new MailTemplateContent();
                $message = 'MailTemplate has been inserted';

                $parent = new MailTemplate();
                if (!empty($params['tag'])) $parent->setTag($params['tag']);
                else $errors[] = 'Tag cannot be empty';
                $mailTemplate->setMailTemplate($parent);
            }

            $mailTemplate->setLocale($locale);
            
            if (!empty($params['name'])) $mailTemplate->setName($params['name']);
            else $errors[] = 'Name cannot be empty';

            if (!empty($params['subject'])) $mailTemplate->setSubject($params['subject']);
            else $errors[] = 'Subject cannot be empty';

            if (isset($params['body'])) $mailTemplate->setBody($params['body']);

            if (!empty($errors)) {

                $response = [
                    'success' => false,
                    'message' => $errors,
                ];
                $json = json_encode($response);
                return $this->json($json);
            }

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $this->serializer->serialize($mailTemplate, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($mailTemplate);
            $em->flush();
            $id = $mailTemplate->getId();

            $log->add('MailTemplate', $id, $logMessage, $logComment);

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
    * @Route("/api/v1/mail/template/delete/", name="api_mail_template_delete", methods={"PUT"})
    * @Route("/api/v1/mail/template/delete/{id}/", name="api_mail_template_delete_multiple", methods={"DELETE"})
    */
    final public function deleteTemplate(int $id=0, LogService $log)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        if (!empty($params['ids'])) $toRemove = $params['ids'];
        elseif (!empty($id)) $toRemove = array($id);

        if (!empty($toRemove)) {
            foreach($toRemove as $mailTemplateId) {

                $em = $this->getDoctrine()->getManager();
                $mailTemplate = $em->getRepository(MailTemplateContent::class)->find($mailTemplateId);

                if ($mailTemplate) {
                    $logMessage = '<i>Data:</i><br>';
                    $logMessage .= $this->serializer->serialize($mailTemplate, 'json');

                    $log->add('MailTemplate', $id, $logMessage, 'Delete');

                    $mailTemplate = $mailTemplate->getMailTemplate();

                    $em->remove($mailTemplate);
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
