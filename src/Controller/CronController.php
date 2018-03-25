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
use Cron\CronExpression;

use App\Entity\Cron;
use App\Form\CronForm;
use App\Service\LogService;


class CronController extends Controller
{
    /**
     * @Route("/{_locale}/admin/cron/", name="cron"))
     */
     final public function list(TranslatorInterface $translator)
     {
         return $this->render('cron/admin/list.html.twig', array(
             'page_title' => $translator->trans('Crons'),
             'can_add' => true,
             'can_edit' => true,
             'can_delete' => true,
         ));
     }

     /**
      * @Route("/{_locale}/admin/cron/ajaxlist/", name="cron_ajaxlist"))
      */
     final public function ajaxlist(Request $request)
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

         $qb = $this->getDoctrine()->getRepository(Cron::class)->createQueryBuilder('c');
         $qb->select('count(c.id)');
         $qb->where($whereString);
         $count = $qb->getQuery()->getSingleScalarResult();

         if (empty($limit)) {
             $crons = $this->getDoctrine()
                 ->getRepository(Cron::class)
                 ->findBy($where, array($sort_column => $sort_direction));
         } else {
             $crons = $this->getDoctrine()
                 ->getRepository(Cron::class)
                 ->findBy($where, array($sort_column => $sort_direction), $limit, $offset);
         }

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);

         $json = array(
             'total' => 6,
             'data' => $crons
         );

         $json = $serializer->serialize($json, 'json');

         return $this->json($json);
     }

     /**
      * @Route("/{_locale}/admin/cron/add/", name="cron_add"))
      * @Route("/{_locale}/admin/cron/edit/{id}/", name="cron_edit"))
      */
     final public function edit($id=0, Request $request, TranslatorInterface $translator, LogService $log)
     {
        //$this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, 'Unable to access this page!');

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $logMessage = '';
        $logComment = 'Insert';

        if (!empty($id)) {
            $cron = $this->getDoctrine()
                ->getRepository(Cron::class)
                ->find($id);
            if (!$cron) {
                $cron = new Cron();
                $this->addFlash(
                    'error',
                    $translator->trans('The requested cron does not exist!')
                );
            } else {
                $logMessage .= '<i>Old data:</i><br>';
                $logMessage .= $serializer->serialize($cron, 'json');
                $logMessage .= '<br><br>';
                $logComment = 'Update';

            }
        } else {
            $cron = new Cron();
        }

        $form = $this->createForm(CronForm::class, $cron);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // perform some action...
            $cron = $form->getData();

            $logMessage .= '<i>New data:</i><br>';
            $logMessage .= $serializer->serialize($cron, 'json');

            $em = $this->getDoctrine()->getManager();
            $em->persist($cron);
            $em->flush();
            $id = $cron->getId();

            $log->add('Cron', $id, $logMessage, $logComment);

            $this->addFlash(
                'success',
                $translator->trans('Your changes were saved!')
            );
            return $this->redirectToRoute('cron_edit', array('id' => $id));
        }

        if (!empty($id)) $title = $translator->trans('Edit cron');
        else $title = $translator->trans('Add cron');

        return $this->render('common/form.html.twig', array(
            'form' => $form->createView(),
            'page_title' => $title,
        ));
     }

     /**
      * @Route("/{_locale}/admin/cron/delete/{id}/", name="cron_delete"))
      */
     final public function delete($id, LogService $log)
     {
         $em = $this->getDoctrine()->getManager();
         $cron = $em->getRepository(Cron::class)->find($id);

         $encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);
         $logMessage = '<i>Data:</i><br>';
         $logMessage .= $serializer->serialize($cron, 'json');

         $log->add('Cron', $id, $logMessage, 'Delete');

         $em->remove($cron);
         $em->flush();

         return new Response(
             '1'
         );
     }

     /**
      * @Route("/cron/", name="cron_run")
      */
     final public function cron(Request $request)
     {
         $start = time();

         $crons = $this->getDoctrine()
             ->getRepository(Cron::class)
             ->findBy(['active' => 1]);

        if ($crons) {
            foreach($crons as $cron) {

                $script = 'http://yuna.test' . $cron->getScript();

                $expression = $cron->getMinute() . ' ';
                $expression .= $cron->getHour() . ' ';
                $expression .= $cron->getDay() . ' ';
                $expression .= $cron->getMonth() . ' ';
                $expression .= $cron->getDayOfWeek();

                $cronExpression = CronExpression::factory($expression);
                $count = $cron->getRunCount();
                $lastRun = $cron->getLastRun()->format('Y-m-d H:i:s');
                $nextRun = $cronExpression->getNextRunDate($lastRun)->format('Y-m-d H:i:s');


                if ($start >= strtotime($nextRun)) {

                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $script);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    // https://stackoverflow.com/questions/2190854/sending-post-requests-without-waiting-for-response
                    //curl_setopt($curl, CURLOPT_TIMEOUT_MS, 1);
                    //curl_setopt($curl, CURLOPT_NOSIGNAL, 1);
                    curl_exec($curl);
                    curl_close($curl);

                    $cron->setLastRun(new \DateTime(date('Y-m-d H:i:s')));
                    $nextRun = $cronExpression->getNextRunDate(new \DateTime(date('Y-m-d H:i:s')))->format('Y-m-d H:i:s');
                    $cron->setNextRun(new \DateTime($nextRun));
                    $cron->setRunCount($count+1);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($cron);
                    $em->flush();
                }
            }
        }

        $cronlog = 'Cron init: ' . date('Y-m-d H:i:s') . PHP_EOL;

        return new Response($cronlog);
     }
}
