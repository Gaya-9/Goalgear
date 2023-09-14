<?php

namespace App\Controller;

use Stripe\StripeClient;
use Stripe\Checkout\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PaymentController extends AbstractController
{
    #[Route('/payment', name: 'app_payment')]
    public function index(): Response
    {
        return $this->render('payment/index.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }

    private function createStripeSession()
    {
        
        $stripe = new StripeClient('sk_test_51NBHWKBfJ1Xx5z1jRbcvBtTi149UVT66zui8cBuegX85mzR29IbeVoCXGFaUYepHCeslNg3akodl1bDTrTKEVbl4004mG3ZRxL');

        $session = Session::create([
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'T-shirt',
                    ],
                    'unit_amount' => 2000,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('success_url', [], true), // Use Symfony route generation
            'cancel_url' => $this->generateUrl('cancel_url', [], true), // Use Symfony route generation
        ]);

        return $session;
    }

    /**
     * @Route("/checkout", name="payment_checkout")
     */
    public function checkout()
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        try {
            $session = $this->createStripeSession();
            return $this->redirect($session->url);
        } catch (\Exception $e) {
            // Handle Stripe API error
            return $this->redirectToRoute('cancel_url');
        }
    }

    /**
     * @Route("/payment/success", name="success_url")
     */
    public function successUrl()
    {
        return $this->render("payment/success.html.twig");
    }

    /**
     * @Route("/payment/cancel", name="cancel_url")
     */
    public function cancelUrl()
    {
        return $this->render("payment/cancel.html.twig");
    }
}

