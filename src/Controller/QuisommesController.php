<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuisommesController extends AbstractController
{
    #[Route('/quisommes', name: 'app_quisommes')]
    public function index(): Response
    {
        return $this->render('quisommes/index.html.twig', [
            'controller_name' => 'QuisommesController',
        ]);
    }
}
