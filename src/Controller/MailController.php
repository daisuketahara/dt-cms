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

use App\Entity\Locale;
use App\Entity\MailQueue;
use App\Entity\MailTemplate;
use App\Form\MailTemplateForm;
use App\Service\LogService;
use App\Service\SettingService;


class MailController extends Controller
{
    /**
     * @Route("/{_locale}/admin/mail/queue/", name="mail_queue"))
     */
     final public function list(TranslatorInterface $translator)
     {
         return $this->render('mail/admin/queue.html.twig', array(
             'page_title' => $translator->trans('Mail Queue'),
             'can_delete' => true,
         ));
     }

     /**
      * @Route("/{_locale}/admin/mail/queue/get/", name="mail_queue_get"))
      */
     final public function queuelist(Request $request)
     {
         $sort_column = $request->request->get('sortColumn', 'id');
         $sort_direction = strtoupper($request->request->get('sortDirection', 'desc'));
         $limit = $request->request->get('limit', 15);
         $offset = $request->request->get('offset', 0);
         $filter = $request->request->get('filter', '');

         $where = array();
         $whereString = '1=1';
         $filter = explode('&', $filter);
         if (!empty($filter))
         foreach($filter as $filter_item) {
             $filter_item = explode('=', $filter_item);
             if (!empty($filter_item[1])) {
                 $where[$filter_item[0]] = $filter_item[1];
                 $whereString .= " AND `" . $filter_item[0] . "`='" . $filter_item[1] . "'";
             }
         }

         $qb = $this->getDoctrine()->getRepository(MailQueue::class)->createQueryBuilder('m');
         $qb->select('count(m.id)');
         $qb->where($whereString);
         $count = $qb->getQuery()->getSingleScalarResult();

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
      * @Route("/{_locale}/admin/mail/queue/delete/{id}/", name="mail_queue_delete"))
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
      * @Route("/cron/mail/queue/send/", name="mail_queue_send")
      */
     final public function send(LogService $log, SettingService $setting, \Swift_Mailer $mailer)
     {

         $em = $this->getDoctrine()->getManager();
         $mails = $em->getRepository(MailQueue::class)->findToSend();

         $success = 0;
         $fail = 0;
         foreach($mails as $mail) {

             $from = $mail->getFromEmail();
             if (!$from) $from = $setting->getSetting('site.email.from');

             $message = (new \Swift_Message($mail->getSubject()))
                    ->setFrom($from)
                    ->setTo($mail->getToEmail())
                    ->setBody($mail->getBody());
             if ($mailer->send($message)) {
                $mail->setSendDate(new \DateTime("now"));
                $mail->setStatus(1);
                $success++;
             } else {
                $mail->setStatus(-1);
                $fail++;
             }

             $em = $this->getDoctrine()->getManager();
             $em->persist($mail);
             $em->flush();
         }


         $logMessage = $success . ' send emails cleared from queue. ' . $fail . ' failed attempts.';

         if (!empty($success) || !empty($fail)) $log->add('Mail Queue', 0, $logMessage, 'Send mail queue');

         return new Response(
             $success . ' send emails cleared from queue. ' . $fail . ' failed attempts.'
         );
     }

     /**
      * @Route("/cron/mail/queue/clear/", name="mail_queue_clear")
      */
     final public function clear(SettingService $setting, LogService $log)
     {
         $days = $setting->getSetting('email.history.days');

         $em = $this->getDoctrine()->getManager();
         $mail = $em->getRepository(MailQueue::class)->deleteOldRecords($days);

         $logMessage = 'Emails cleared from queue';

         //$log->add('Mail Queue', 0, $logMessage, 'Clear mail queue');

         return new Response(
             'Emails cleared from queue'
         );
     }

     /**
      * @Route("/{_locale}/admin/mail/template/", name="mail_template"))
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
       * @Route("/{_locale}/admin/mail/template/get/", name="mail_template_get"))
       */
      final public function getTemplate(Request $request)
      {
          $sort_column = $request->request->get('sortColumn', 'id');
          $sort_direction = strtoupper($request->request->get('sortDirection', 'desc'));
          $limit = $request->request->get('limit', 15);
          $offset = $request->request->get('offset', 0);
          $filter = $request->request->get('filter', '');

          $locale = $this->getDoctrine()
              ->getRepository(Locale::class)
              ->findOneBy(array('locale' => $this->container->getParameter('kernel.default_locale')));

          $where = array('locale' => $locale->getId());
          $whereString = '1=1';
          $filter = explode('&', $filter);
          if (!empty($filter))
          foreach($filter as $filter_item) {
              $filter_item = explode('=', $filter_item);
              if (!empty($filter_item[1])) {
                  $where[$filter_item[0]] = $filter_item[1];
                  $whereString .= " AND `" . $filter_item[0] . "`='" . $filter_item[1] . "'";
              }
          }

          $qb = $this->getDoctrine()->getRepository(MailTemplate::class)->createQueryBuilder('m');
          $qb->select('count(m.id)');
          $qb->where($whereString);
          $count = $qb->getQuery()->getSingleScalarResult();

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
     * @Route("/{_locale}/admin/mail/template/edit/{id}/", name="mail_template_edit"))
     * @Route("/{_locale}/admin/mail/template/edit/{id}/translate/{localeId}/", name="mail_template_edit_translate"))
     */
    final public function edit($id=0, $localeId=0, Request $request, TranslatorInterface $translator, LogService $log)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $logMessage = '';
        $logComment = 'Insert';

         if (empty($localeId)) {
             $localeSlug = $this->container->getParameter('kernel.default_locale');
             $locale = $this->getDoctrine()
                 ->getRepository(Locale::class)
                 ->findOneBy(array('locale' => $localeSlug));
             if ($locale) $localeId = $locale->getId();
         }

         if (empty($locale)) {
             $locale = $this->getDoctrine()
                 ->getRepository(Locale::class)
                 ->find($localeId);
         }

         if (!empty($id)) {

             if (!$locale->getDefault()) {

                 $template = $this->getDoctrine()
                     ->getRepository(MailTemplate::class)
                     ->findOneBy(array('defaultId' => $id, 'locale' => $localeId));

                 if (!$template) $template = new MailTemplate();

                 $template->setDefaultId($id);

             } else {

                 $template = $this->getDoctrine()
                     ->getRepository(MailTemplate::class)
                     ->find($id);

                 if (!$template) {
                     $template = new MailTemplate();
                     $this->addFlash(
                         'error',
                         $translator->trans('The requested mail template does not exist!')
                     );
                 }
             }
             if ($template) {
                 $logMessage .= '<i>Old data:</i><br>';
                 $logMessage .= $serializer->serialize($template, 'json');
                 $logMessage .= '<br><br>';
                 $logComment = 'Update';

             }
         } else {
             $template = new MailTemplate();
         }

         $form = $this->createFormBuilder();
         $form = $form->getForm();
         $form->handleRequest($request);

         if ($request->isMethod('POST')) {

            if (!$locale->getDefault() && empty($id)) {

                $localeSlugDefault = $this->container->getParameter('kernel.default_locale');
                $localeDefault = $this->getDoctrine()
                    ->getRepository(Locale::class)
                    ->findOneBy(array('locale' => $localeSlugDefault));
                $templateDefault = new MailTemplate();

                $templateDefault->setLocale($localeDefault);
                $templateDefault->setName($request->request->get('mail-name', ''));
                $templateDefault->setSubject($request->request->get('mail-subject', ''));
                $templateDefault->setBody($request->request->get('mail-body', ''));

                $em = $this->getDoctrine()->getManager();
                $em->persist($templateDefault);
                $em->flush();
                $id = $templateDefault->getId();
                $template->setDefaultId($id);
            }

            $template->setLocale($locale);
            $template->setName($request->request->get('mail-name', ''));
            $template->setSubject($request->request->get('mail-subject', ''));
            $template->setBody($request->request->get('mail-body', ''));

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($template, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($template);
            $em->flush();

            $log->add('Mail template', $id, $logMessage, $logComment);

             $this->addFlash(
                 'success',
                 $translator->trans('Your changes were saved!')
             );

             if (!empty($localeId)) return $this->redirectToRoute('mail_template_edit_translate', array('id' => $id, 'localeId' => $localeId));
             else return $this->redirectToRoute('mail_template_edit', array('id' => $id));
         }

         if (!empty($id)) $title = $translator->trans('Edit mail template');
         else $title = $translator->trans('Add mail template');

         $locales = $this->getDoctrine()
             ->getRepository(Locale::class)
             ->findAll();

         return $this->render('mail/admin/edit.html.twig', array(
             'page_title' => $title,
             'id' => $id,
             'name' => $template->getName(),
             'subject' => $template->getSubject(),
             'body' => $template->getBody(),
             'variables' => explode(',', $template->getVariables()),
             'locale' => $locale,
             'default_locale' => $this->container->getParameter('kernel.default_locale'),
             'locales' => $locales,
         ));
      }

      /**
       * @Route("/{_locale}/admin/mail/template/delete/{id}/", name="mail_template_delete"))
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
