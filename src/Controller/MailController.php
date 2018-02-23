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

use App\Entity\MailQueue;
use App\Entity\MailTemplate;
use App\Form\MailTemplateForm;
use App\Service\LogService;


class MailController extends Controller
{
    /**
     * @Route("/{_locale}/admin/mail/queue", name="mail_queue")
     */
     final public function list(TranslatorInterface $translator)
     {
         return $this->render('mail/admin/queue.html.twig', array(
             'page_title' => $translator->trans('Mail Queue'),
             'can_delete' => true,
         ));
     }

     /**
      * @Route("/{_locale}/admin/mail/queue/list", name="mail_queue_list")
      */
     final public function queuelist(Request $request)
     {
         $sort_column = $request->request->get('sortColumn', 'id');
         $sort_direction = strtoupper($request->request->get('sortDirection', 'desc'));
         $limit = $request->request->get('limit', 15);
         $offset = $request->request->get('offset', 0);
         $filter = $request->request->get('filter', '');

         $where = array();
         $filter = explode('&', $filter);
         if (!empty($filter))
         foreach($filter as $filter_item) {
             $filter_item = explode('=', $filter_item);
             if (!empty($filter_item[1])) $where[$filter_item[0]] = $filter_item[1];
         }

         if (empty($limit)) {
             $mail = $this->getDoctrine()
                 ->getRepository(MailQueue::class)
                 ->findBy($where, array($sort_column => $sort_direction));
         } else {
             $mail = $this->getDoctrine()
                 ->getRepository(MailQueue::class)
                 ->findBy($where, array($sort_column => $sort_direction), $limit, $offset);
         }

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);

         $json = array(
             'total' => 6,
             'data' => $mail
         );

         $json = $serializer->serialize($json, 'json');

         return $this->json($json);
     }

     /**
      * @Route("/{_locale}/admin/mail/queue/delete/{id}", name="mail_queue_delete")
      */
     final public function delete($id, LogService $log)
     {
         $em = $this->getDoctrine()->getManager();
         $mail = $em->getRepository(MailQueue::class)->find($id);

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);
         $logMessage = '<i>Data:</i><br>';
         $logMessage .= $serializer->serialize($mail, 'json');

         $log->add('Mail Queue', $id, $logMessage, 'Delete');

         $em->remove($mail);
         $em->flush();

         return new Response(
             '1'
         );
     }

     /**
      * @Route("/{_locale}/cron/mail/queue/send", name="mail_queue_send")
      */
     final public function send(LogService $log, \Swift_Mailer $mailer)
     {
         $em = $this->getDoctrine()->getManager();
         $mails = $em->getRepository(MailQueue::class)->findToSend();

         $success = 0;
         $fail = 0;
         foreach($mails as $mail) {

             $message = (new \Swift_Message($mail->getSubject()))
                    ->setFrom($mail->getFromEmail())
                    ->setTo($mail->getToEmail())
                    ->setBody(
                        $this->renderView(
                            'mail.html.twig',
                            array('body' => $mail->getBody())
                        ),
                        'text/html'
                    );
             if ($mailer->send($message)) {
                $mail->setSendDate(new \DateTime("now"));
                $mail->setStatus(1);
                $count++;
            } else {
                $mail->setStatus(-1);
                $fail++;
            }

             $em = $this->getDoctrine()->getManager();
             $em->persist($mail);
             $em->flush();
         }


         $logMessage = $count . ' send emails cleared from queue. ' . $fail . ' failed attempts.';

         $log->add('Mail Queue', 0, $logMessage, 'Send mail queue');

         return new Response(
             $count . ' send emails cleared from queue. ' . $fail . ' failed attempts.'
         );
     }

     /**
      * @Route("/{_locale}/cron/mail/queue/clear", name="mail_queue_clear")
      */
     final public function clear(LogService $log)
     {
         $em = $this->getDoctrine()->getManager();
         $mail = $em->getRepository(MailQueue::class)->findToDelete();

         $logMessage = 'Emails cleared from queue';
         //var_dump($mail);exit;

         $log->add('Mail Queue', 0, $logMessage, 'Clear mail queue');

         return new Response(
             'Emails cleared from queue'
         );
     }



     /**
      * @Route("/{_locale}/admin/mail/template", name="mail_template")
      */
      final public function template(TranslatorInterface $translator)
      {
          return $this->render('mail/admin/template.html.twig', array(
              'page_title' => $translator->trans('Mail templates'),
              'can_add' => true,
              'can_edit' => true,
              'can_delete' => true,
          ));
      }

      /**
       * @Route("/{_locale}/admin/mail/template/list", name="mail_template_list")
       */
      final public function ajaxlist(Request $request)
      {
          $sort_column = $request->request->get('sortColumn', 'id');
          $sort_direction = strtoupper($request->request->get('sortDirection', 'desc'));
          $limit = $request->request->get('limit', 15);
          $offset = $request->request->get('offset', 0);
          $filter = $request->request->get('filter', '');

          $where = array();
          $filter = explode('&', $filter);
          if (!empty($filter))
          foreach($filter as $filter_item) {
              $filter_item = explode('=', $filter_item);
              if (!empty($filter_item[1])) $where[$filter_item[0]] = $filter_item[1];
          }

          if (empty($limit)) {
              $template = $this->getDoctrine()
                  ->getRepository(MailTemplate::class)
                  ->findBy($where, array($sort_column => $sort_direction));
          } else {
              $template = $this->getDoctrine()
                  ->getRepository(MailTemplate::class)
                  ->findBy($where, array($sort_column => $sort_direction), $limit, $offset);
          }

          $encoders = array(new XmlEncoder(), new JsonEncoder());
          $normalizers = array(new ObjectNormalizer());
          $serializer = new Serializer($normalizers, $encoders);

          $json = array(
              'total' => 6,
              'data' => $template
          );

          $json = $serializer->serialize($json, 'json');

          return $this->json($json);
      }

      /**
       * @Route("/{_locale}/admin/mail/template/add", name="mail_template_add")
       * @Route("/{_locale}/admin/mail/template/edit/{id}", name="mail_template_edit")
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
                 ->getRepository(MailTemplate::class)
                 ->find($id);
             if (!$template) {
                 $template = new MailTemplate();
                 $this->addFlash(
                     'error',
                     $translator->trans('The requested mail template does not exist!')
                 );
             } else {
                 $logMessage .= '<i>Old data:</i><br>';
                 $logMessage .= $serializer->serialize($template, 'json');
                 $logMessage .= '<br><br>';
                 $logComment = 'Update';

             }
         } else {
             $template = new MailTemplate();
         }

         $form = $this->createForm(MailTemplateForm::class, $template);

         $form->handleRequest($request);

         if ($form->isSubmitted() && $form->isValid()) {

             // perform some action...
             $template = $form->getData();

             $logMessage .= '<i>New data:</i><br>';
             $logMessage .= $serializer->serialize($template, 'json');

             $em = $this->getDoctrine()->getManager();
             $em->persist($template);
             $em->flush();
             $id = $template->getId();

             $log->add('Mail template', $id, $logMessage, $logComment);

             $this->addFlash(
                 'success',
                 $translator->trans('Your changes were saved!')
             );
             return $this->redirectToRoute('mail_template_edit', array('id' => $id));
         }

         if (!empty($id)) $title = $translator->trans('Edit mail template');
         else $title = $translator->trans('Add mail template');

         return $this->render('common/form.html.twig', array(
             'form' => $form->createView(),
             'page_title' => $title,
         ));
      }

      /**
       * @Route("/{_locale}/admin/mail/template/delete/{id}", name="mail_template_delete")
       */
      final public function delete_template($id, LogService $log)
      {
          $em = $this->getDoctrine()->getManager();
          $template = $em->getRepository(MailTemplate::class)->find($id);

          $encoders = array(new XmlEncoder(), new JsonEncoder());
          $normalizers = array(new ObjectNormalizer());
          $serializer = new Serializer($normalizers, $encoders);
          $logMessage = '<i>Data:</i><br>';
          $logMessage .= $serializer->serialize($template, 'json');

          $log->add('Mail template', $id, $logMessage, 'Delete');

          $em->remove($template);
          $em->flush();

          return new Response(
              '1'
          );
      }





}
