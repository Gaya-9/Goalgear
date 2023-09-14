<?php

namespace App\Controller;

use App\Entity\Produit;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    
    #[Route('/cart', name: 'app_cart')]
    public function index(SessionInterface $session, ManagerRegistry $doctrine): Response
    {
        // On récupère le panier depuis la session s'il existe, sinon on le crée avec un tableau vide
        $panier = $session->get('panier', []);
        $panierData = [];
    
        // On récupère les données de chaque produit du panier en utilisant son ID
        foreach ($panier as $id => $quantity) {
            $product = $doctrine->getRepository(Produit::class)->find($id);
            if ($product) {
                $panierData[] = [
                    "product" => $product,
                    "quantity" => $quantity
                ];
            }
        }
    
        // Calcul du total du panier
        $total = 0;
        foreach ($panierData as $item) {
            $total += $item['product']->getPrix() * $item['quantity'];
        }
    
        // Calcul du total de quantité
        $totalQuantity = array_sum($panier);
    
        return $this->render('cart/index.html.twig', [
            "items" => $panierData,
            "total" => $total,
            "totalQuantity" => $totalQuantity
        ]);
    }
    
    

    /**
     * @Route("cart/add/{id}", name="cart_add")
     */
    public function cartAdd($id, SessionInterface $session)
    {

       #Etape 1 On récupère la session 'panier' si elle existe-sinon elle est crée avec un tableau vide
       $panier = $session->get('panier',[]);
       if(!(empty($panier [$id])))
       {
         $panier[$id]++ ;
       }
       else
       {
        $panier[$id]=1;
       }
       #Etape 2 On ajoute la quantité 1 au produit d'id $id
       
       #Etape 3 On remplace la variable de session panier par le nouveau tableau $panier
       $session->set('panier', $panier);

       //dd($session->get('panier', []));

    return $this->redirectToRoute('app_cart');
        
    }



   /**
     * @Route("/panier/delete/{id}", name="cart_delete")
     */
    public function delete($id, SessionInterface $session)
    {
        #On récupere la session 'panier' si elle existe - sinon elle est créée avec un tableau vide
        $panier = $session->get('panier', []);
        
        #On supprime de la session celui dont on a passé l'id
        if(!empty($panier[$id]))
        {
            $panier[$id]--;

            if($panier[$id] <= 0)
            {
                unset($panier[$id]); //unset pour dépiler de la session
            }
        }

        #On réaffecte le nouveau panier à la session
        $session->set('panier', $panier);

        #On redirige vers le panier
        return $this->redirectToRoute('app_cart');
    }  

}
