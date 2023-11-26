<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Entity\Produit;
use App\Entity\Commandes;
use Stripe\Checkout\Session;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PaymentController extends AbstractController
{
    /**
     * @Route("/payment", name="page_payment")
     */
    public function index(): Response
    {
        return $this->render('payment/index.html.twig', ['controller_name' => 'PaymentController']);
    }

    /**
     * @Route("/checkout", name="payment_checkout", methods={"POST"})
     */
    public function checkout(SessionInterface $session, ManagerRegistry $doctrine): Response
    {
        // Récupération de la clé secrète Stripe depuis les variables d'environnement
        $stripeSK = $_ENV['STRIPE_SECRET_KEY'];

        // Configuration de la clé secrète Stripe
        Stripe::setApiKey($stripeSK);

        // Récupération du panier depuis la session
        $panier = $session->get('panier', []);
        $panierData = [];

        // Récupération des données de chaque produit dans le panier
        foreach ($panier as $id => $quantity) {
            $panierData[] = [
                "product" => $doctrine->getRepository(Produit::class)->find($id),
                "quantity" => $quantity
            ];
        }

// Générer un numéro de commande aléatoire
$numeroCommande = 'CMD_' . strtoupper(uniqid());

$commande = new Commandes();

// Vous pouvez remplir les détails de la commande selon vos besoins
$commande->setUtilisateur($this->getUser()); // Associez l'utilisateur actuel à la commande si nécessaire
$commande->setDateCommande(new \DateTime()); // Ajoutez la date de la commande
$commande->setNumeroCommande($numeroCommande);

// Calculez le total de la commande en fonction des produits du panier
$total = 0;
foreach ($panierData as $value) {
    $total += $value['product']->getPrix() * $value['quantity'];
}
$commande->setTotal($total); // Ajoutez le montant total de la commande

// Enregistrer la commande en base de données
$entityManager = $doctrine->getManager();
$entityManager->persist($commande);
$entityManager->flush();


        // Création des lignes d'articles pour la session Stripe
        $lineItems = [];
        foreach ($panierData as $value) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $value['product']->getNom(),
                    ],
                    'unit_amount' => $value['product']->getPrix() * 100, // Montant en centimes
                ],
                'quantity' => $value['quantity'],
            ];
        }

        // Création de la session de paiement avec Stripe
        $stripeSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->generateUrl('success_url', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('cancel_url', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($stripeSession->url, 303);
    }

    /**
     * @Route("/payment/success", name="success_url")
     */
    public function successUrl(SessionInterface $session): Response
    {
        $session->remove('panier');
        return $this->render("payment/success.html.twig");
    }

    /**
     * @Route("/payment/cancel", name="cancel_url")
     */
    public function cancelUrl(): Response
    {
        return $this->render("payment/cancel.html.twig");
    }



}






