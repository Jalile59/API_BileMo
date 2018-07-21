<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BilemoController extends Controller
{
    /**
     * @Route("/bilemo", name="bilemo")
     */
    
    public function index()
    {
        return $this->render('bilemo/index.html.twig', [
            'controller_name' => 'BilemoController',
        ]);
    }
}
