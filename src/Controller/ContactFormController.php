<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use App\Entity\Contact;
use App\Entity\Locale;
use App\Service\MailService;
use App\Service\SettingService;

class ContactFormController extends Controller
{
    private $mail;
    private $setting;
    private $translator;

    public function __construct(MailService $mail, SettingService $setting, TranslatorInterface $translator)
    {
        $this->mail = $mail;
        $this->setting = $setting;
        $this->translator = $translator;
    }

    /**
     * @Route("/{_locale}/ajax/contact/getform/", name="contact_ajax_get_form"))
     */
    final public function getForm()
    {
        return $this->render('contact/form.html.twig', array());
    }

    /**
     * @Route("/{_locale}/ajax/contact/post/", name="contact_ajax_post_form"))
     */
    final public function postForm(Request $request)
    {

        $phone = $request->request->get('phone', '');
        $email = $request->request->get('email', '');
        $message = $request->request->get('message', '');
        $callback = $request->query->get('callback', '');

        $contact = new Contact();
        $contact->setPhone($phone);
        $contact->setFromEmail($email);
        $contact->setMessage($message);
        $contact->setSendDate(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($contact);
        $em->flush();
        $id = $contact->getId();

        if (!empty($id)) {

            $localeEntity = $this->getDoctrine()
                ->getRepository(Locale::class)
                ->findOneBy(array('locale' => $request->getLocale()));

            if ($localeEntity) $localeId = $localeEntity->getId();
            else $localeId = 1;

            $this->mail->addToQueue(
                $this->setting->getSetting('site.email'),
                'contact-form',
                $localeId,
                array(
                    'phone' => $phone,
                    'email' => $email,
                    'message' => $message,
                )
            );

            $html = '<div class="alert alert-success" role="alert">';
            $html .= $this->translator->trans('Your message has been send.');
            $html .= '</div>';

            $response = array(
                'result' => 'valid',
                'html' => $html,
            );
        } else {

            $html = '<div class="alert alert-error" role="alert">';
            $html .= $this->translator->trans('An error occurred, please try again.');
            $html .= '</div>';

            $response = array(
                'result' => 'failed',
                'html' => $html,
            );
        }
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        //$json = json_encode($response);
        if (!empty($callback)) echo $callback . '(' . $serializer->serialize($response, 'json') . ')';
        else echo $serializer->serialize($response, 'json');
        exit;
    }
}
