<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;



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
    
    /**
     * @Route("/mobiles", name="mobiles_create")
     * @method({"POST"})
     * @param Request $request
     */
    
    public function post (Request $request) {
        
        
       $data = $request->getContent();
        
      
       $mobile = $this->get('serializer')->deserialize($data,'App\Entity\Product', 'json');
        
       $em = $this->getDoctrine()->getManager();
       
       $em->persist($mobile);
       $em->flush();
       
       return New Response('', Response::HTTP_CREATED);
        
        ;
    }
    
    /**
     * @Route("/ListMobile", name="liste_mobile")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    
    public function getMobiles() {
        
        
        $em =$this->getDoctrine()->getManager();
        $listMobile = $em->getRepository(Product::class)->findAll();
        
        //dump($listMobile);
        
        $data = $this->get('serializer')->serialize($listMobile, 'json');
        
        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
        
        ;
    }
}
