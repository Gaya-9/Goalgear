<?php




namespace App\Controller;

use Swift;
use Swift_Mailer;
use App\Entity\ResetToken;
use App\Form\ResetPasswordType;
use App\Form\ForgotPasswordType;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ForgotpasswordController extends AbstractController
{
    /**
     * @Route("/forgot-password", name="forgot_password")
     */
    public function forgotPassword(Request $request, Swift_Mailer $mailer): Response
    {
        $form = $this->createForm(ForgotPasswordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $user = $form->getData();

            // Generate a unique reset token and save it in the database
            $resetToken = new ResetToken($user);
            $entityManager->persist($resetToken);
            $entityManager->flush();

            // Send reset password email
            $message = (new \Swift_Message('Password Reset'))
                ->setFrom('noreply@example.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'emails/reset_password.html.twig',
                        ['token' => $resetToken->getToken()]
                    ),
                    'text/html'
                );

            $mailer->send($message);

            $this->addFlash('success', 'An email has been sent with instructions to reset your password.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgot_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/reset-password/{token}", name="reset_password")
     */
    public function resetPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder, string $token): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $resetToken = $entityManager->getRepository(ResetToken::class)->findOneBy(['token' => $token]);

        if (!$resetToken || $resetToken->isExpired()) {
            $this->addFlash('danger', 'Invalid or expired token.');
            return $this->redirectToRoute('app_login');
        }

        $user = $resetToken->getUser();

        $form = $this->createForm(ResetPasswordType::class, null, ['validation_groups' => ['Default', 'password_reset']]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordEncoder->encodePassword($user, $form->get('plainPassword')->getData()));
            $entityManager->remove($resetToken);
            $entityManager->flush();

            $this->addFlash('success', 'Your password has been successfully reset.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
