<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

use App\Entity\Contact;
use App\Entity\Locale;
use App\Service\MailService;
use App\Service\SettingService;

class ContactController extends AbstractController
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
    * @Route("/api/v1/contact/post/", name="api_contact_post"))
    */
    final public function send(Request $request)
    {
        $params = json_decode(file_get_contents("php://input"),true);
        $contact = new Contact();

        if (!empty($params['email'])) $contact->setFromEmail($params['email']);
        else $errors[] = 'Email cannot be empty';

        if (isset($params['phone'])) $contact->setPhone($params['phone']);

        if (!empty($params['message'])) $contact->setMessage($params['message']);
        else $errors[] = 'Message cannot be empty';

        if (!empty($params['locale'])) {
            $localeTag = $params['locale'];
            $locale = $this->getDoctrine()
                ->getRepository(Locale::class)
                ->findOneBy(['locale' => $localeTag]);
        }

        if (!$locale) $locale = $this->getDoctrine()->getRepository(Locale::class)->getDefaultLocale();

        $contact->setSendDate(new \DateTime());

        if (!empty($errors)) {

            $response = [
                'success' => false,
                'message' => $errors,
            ];
            return $this->json($response);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($contact);
        $em->flush();
        $id = $contact->getId();

        if (!empty($id)) {

            $this->mail->addToQueue(
                $this->setting->get('mail.from'),
                'contact-form',
                $locale->getId(),
                array(
                    'phone' => $contact->getPhone(),
                    'email' => $contact->getFromEmail(),
                    'message' => $contact->getMessage(),
                )
            );

            $response = [
                'success' => true,
                'message' => $this->translator->trans('Your message has been send.'),
            ];
        } else {
            $response = [
                'success' => false,
                'message' => $this->translator->trans('An error occurred, please try again.'),
            ];
        }

        return $this->json($response);
    }
}
