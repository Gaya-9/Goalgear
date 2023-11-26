<?php




namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ForgotpasswordController extends AbstractController
{
    /**
     * @Route("/forgot-password", name="forgot_password", methods={"GET", "POST"})
     */
    public function forgotPassword(Request $request, MailerInterface $mailer): Response
    {
        $error = null; 
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
    
            // Valider que l'email n'est pas vide et qu'il a un format valide
            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $token = bin2hex(random_bytes(32));
                // Créer et envoyer l'e-mail
                $resetLink = $this->generateUrl('reset_password', ['token' => $token], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
    
                $email = (new Email())
                    ->from('votre_email@example.com')
                    ->to($email)
                    ->subject('Réinitialisation de mot de passe')
                    ->html('<p>Vous avez demandé une réinitialisation de mot de passe. Cliquez sur le lien suivant pour procéder à la réinitialisation : <a href="' . $resetLink . '">' . $resetLink . '</a></p>');
    
                $mailer->send($email);
    
                // Redirection vers une page de confirmation
                return $this->redirectToRoute('confirmation');
            } else {
                // Gérer le cas où l'email est invalide ou vide
                // Rediriger vers une page d'erreur ou afficher un message à l'utilisateur
                return $this->render('password_reset/forgot_password.html.twig', [
                    'error' => $error, // Passez la variable error à la vue
                ]);
            }
        }
    
        // Si c'est un simple accès à la page sans soumission de formulaire
        return $this->render('password_reset/forgot_password.html.twig', [
            'error' => $error, // Passez la variable error à la vue
        ]);
    }
    
/**
 * @Route("/reset-password/{token}", name="reset_password", methods={"GET", "POST"})
 */
public function resetPassword(Request $request, $token, UserPasswordEncoderInterface $passwordEncoder): Response
{
    // Recherchez l'utilisateur associé au token dans la base de données
    $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['resetToken' => $token]);

    // Vérifiez si l'utilisateur existe et si le token est valide
    if (!$user || !$user->isResetTokenValid()) {
        // Traitez le cas où le token n'est pas valide
        // Par exemple, redirigez vers une page d'erreur ou de réinitialisation expirée
        return $this->redirectToRoute('invalid_reset');
    }

    // Affichez un formulaire pour que l'utilisateur entre un nouveau mot de passe
    $form = $this->createForm(ResetPasswordFormType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Récupérez le nouveau mot de passe depuis le formulaire
        $newPassword = $form->get('password')->getData();

        // Encodez le nouveau mot de passe
        $encodedPassword = $passwordEncoder->encodePassword($user, $newPassword);

        // Mettez à jour le mot de passe de l'utilisateur dans la base de données
        $user->setPassword($encodedPassword);
        $user->setResetToken(null); // Effacez le token de réinitialisation
        // Enregistrez les modifications dans la base de données
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        // Redirigez vers une page de confirmation de réinitialisation réussie
        return $this->redirectToRoute('reset_confirmation');
    }

    // Affichez le formulaire de réinitialisation
    return $this->render('password_reset/reset_password.html.twig', [
        'form' => $form->createView(),
    ]);
}

    /**
     * @Route("/confirmation", name="confirmation")
     */
    public function confirmation(): Response
    {
        return $this->render('password_reset/confirmation.html.twig');
    }

    /**
     * @Route("/reset-confirmation", name="reset_confirmation")
     */
    public function resetConfirmation(): Response
    {
        return $this->render('password_reset/reset_confirmation.html.twig');
    }
}
