<?php

namespace App\Controller;


use App\Entity\Contact;
use App\Form\ContactType;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mailer\Mailer;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, ManagerRegistry $doctrine, MailerInterface $mailer ): Response
    {
        $contact = new Contact;

        $formContact = $this->createForm(ContactType::class, $contact);

        $formContact->handleRequest($request);
        if($formContact->isSubmitted() && $formContact->isValid())
        {
            $entityManager = $doctrine->getManager();

            $entityManager->persist($contact);
            $entityManager->flush();

            #étape: Envoie de l'email
            $email = (new TemplatedEmail())
            ->from($contact->getMail())
            ->to('contact@monsite.com')
            ->subject('Demande de contact')

            // path of the Twig template to render
            ->htmlTemplate('emails/contact.html.twig')

            // pass variables (name => value) to the template
            ->context([
                   "contact" => $contact
            ]);

              $mailer->send($email);


            #on créé une maessage flash
            $this->addFlash('contact_success', "Le mail a bien été envoyé !");

        }
        return $this->render('contact/index.html.twig', [

            "formContact" => $formContact->createView()
        ]);
    }
    
}  
