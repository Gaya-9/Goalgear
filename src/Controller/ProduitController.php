<?php

namespace App\Controller;

use App\Entity\Produit;

use App\Form\ProduitType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ProduitController extends AbstractController
{
    /**
     * @Route("/produit", name="app_produit")
     */
    public function produit(): Response
    {
        return $this->render('produit/index.html.twig', [
            'controller_name' => 'ProduitController',
        ]);
    }


    /**
     * @Route("/produit/ajout", name="produit_ajout")
     */
    public function ajout(Request $request, ManagerRegistry $doctrine)
    {
    //  $this->denyAccessUnlessGranted('ROLE_ADMIN');
     $produit= new Produit;
     

     $formProduit= $this->createForm(ProduitType::class, $produit);
     $formProduit->handleRequest($request);

    if ($formProduit->isSubmitted() && $formProduit->isValid())
    {
        $entityManager = $doctrine->getManager();


            $entityManager->persist($produit);
            $entityManager->flush();
    
            return $this->redirectToRoute('produit_list');        

           
    }
        return $this->render('Crud/form-ajout.html.twig', [
          "formProduit" =>  $formProduit->createView()
    ]);
    }




    /**
     * @Route("/produit/modif/{id}", name="produit_modif")
     */
    public function modif($id, Request $request, ManagerRegistry $doctrine)
    {
        
        // $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $produit = $doctrine->getRepository(Produit::class)->find($id);
        $formProduit = $this->createForm(ProduitType::class, $produit);
        $formProduit->handleRequest($request);

        if ($formProduit->isSubmitted() && $formProduit->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->flush();

            // Rediriger vers une autre page ou afficher un message de confirmation
            return $this->redirectToRoute('produit_list'); 
        }

        return $this->render('Crud/form-edit.html.twig', [
            'formProduit' => $formProduit->createView()
        ]);
    }



    /**
     * @Route("/produit/list", name="produit_list")
     */ 
    public function readAll(ManagerRegistry $doctrine)
    {
        // $this->denyAccessUnlessGranted('ROLE_ADMIN');

        //dd($this->getUser()->getEmail());

        #Etape 1: Récuperer tous les livres
        $produits = $doctrine->getRepository(Produit::class)->findAll();

        return $this->render("Crud/list.html.twig",[
            'produits'=>$produits
        ]);

    }


    /**
     * @Route("/produit/delete/{id}", name="produit_delete")
     */
    public function supprimer($id, ManagerRegistry $doctrine)
    {
    // $this->denyAccessUnlessGranted('ROLE_ADMIN');
     
    $entityManager = $doctrine->getManager();

    
    $produit = $doctrine->getRepository(Produit::class)->find($id);

    
    $entityManager->remove($produit);

     
    $entityManager->flush();

    $this->addFlash('produit_delete_success', "Le produit a bien été supprimé !");

    return $this->redirectToRoute('produit_list');

    }


    /**
     * @Route("/all", name="all_products")
     */
    public function home(ManagerRegistry $doctrine)
    {
        $produits = $doctrine->getRepository(Produit::class)->findAll();
        
    return $this->render("produit/index.html.twig",[
        'produits'=>$produits
    ]);
    }
}

