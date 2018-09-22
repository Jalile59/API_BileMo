<?php

namespace App\Controller;



use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Put;

use App\Entity\Client;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\service\Tools;
use Symfony\Component\Cache\Simple\FilesystemCache;

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
     * @Put(
     *  path="/api/product",
     *  name="app_product_create"
     * )
     * 
     * @IsGranted("ROLE_SUPER_ADMIN", statusCode=404, message="You are no access")
     * 
     * @View
     */
    
    public function createProduct (Request $request) {
        
        
       $data = $request->getContent();
       
       $cache = new FilesystemCache();
      
       $mobile = $this->get('serializer')->deserialize($data,'App\Entity\Product', 'json');
        
       $em = $this->getDoctrine()->getManager();
       
       $em->persist($mobile);
       $em->flush();
       
       $cache->clear(); //suprimme tous le cas cache en cas d'ajout produit
       
       return New Response('', Response::HTTP_CREATED);
        
        ;
    }
    
    /**
     * @Get(
     *     path = "api/product/{id}",
     *     name = "app_product_show",
     *     requirements = {"id"="\d+"}
     * )
     * @View
     */
    public function getproduct($id, Tools $tools)
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $cache = new FilesystemCache();
        
        $product = $em->getRepository(Product::class)->find($id);
        
        if ($product){
            
            if(!$cache->has($id)) // vérification id objet existe dans le cache.
            {
                $tools->incache($id, $product); // objet dans le cache.
                
                return $product;
                
            }else{
                
                $product = $cache->get($id); // récuperation de l'objet depuis le cache.
                
                return $product;
            }
            
            
        }else{
            
            return new Response('product no found', Response::HTTP_BAD_REQUEST);
        }
        
        
    }
    
    /**
     * @Get(
     *     path = "/api/products/{page}",
     *     name = "app_produc_getall",
     *     requirements = {"page"="\d+"}
     * )
     */
    
    public function getAllProducts($page, Request $request, Tools $tools) {
        
        
        $em =$this->getDoctrine()->getManager();
        $cache = new FilesystemCache();
        
        $url = $tools->getUrl($request);
        
        $listMobile = $em->getRepository(Product::class)->getall_paginat($page);
        
        if($listMobile){
            
            if(!$cache->has(md5($url))) //vérification si id objet déja en cache.
            {
                $data = $this->get('serializer')->serialize($listMobile, 'json');
                
                $response = new Response($data);
                $response->headers->set('Content-Type', 'application/json');
                
                $tools->incache(md5($url), $response); //mise en cache de l'objet.
                                
                return $response;
            }else{
                
                $response = $cache->get(md5($url)); //récuperation objet depuis le cache.
                               
                return $response;
            }

            
                    
        }else{
            
           
            return new Response('product not found', Response::HTTP_BAD_REQUEST);
        }
        

        
            }
            


    }
    
    

