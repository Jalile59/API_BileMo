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
use App\Entity\Client;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;



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
     * @Post(
     *  path="/api/product",
     *  name="app_product_create"
     * )
     * 
     * @View
     */
    
    public function createProduct (Request $request) {
        
        
       $data = $request->getContent();
        
      
       $mobile = $this->get('serializer')->deserialize($data,'App\Entity\Product', 'json');
        
       $em = $this->getDoctrine()->getManager();
       
       $em->persist($mobile);
       $em->flush();
       
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
    public function getproduct($id)
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $product = $em->getRepository(Product::class)->find($id);
        
        if ($product){
            
            return $product;
        }else{
            
            return new Response('product no found', Response::HTTP_BAD_REQUEST);
        }
        
        
    }
    
    /**
     * @Get(
     *     path = "/api/products",
     *     name = "app_produc_getall"
     * )
     */
    
    public function getMobiles() {
        
        
        $em =$this->getDoctrine()->getManager();
        $listMobile = $em->getRepository(Product::class)->findAll();
        
        if($listMobile){
            
            $data = $this->get('serializer')->serialize($listMobile, 'json');
            
            $response = new Response($data);
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;
            
                       
        }else{
            
            $error = ['error'=> 'product not found'];
            
            $data = $this->get('serializer')->serialize($error, 'json');
            
            $reponse = new Response($data);
            
            $reponse->headers->set('Content-Type', 'application/json');
            
            return $reponse;
        }
        

        
            }
            
    
    /**
     * @Route("GET/listeUsers/{nameComapagny}", name="listeUsersByCompany")
     * @param string nameComapagny
     * @return \Symfony\Component\HttpFoundation\Response
     */
    
    public function getUserByNameCompany($nameComapagny){
        
        
        
       
        $em2 = $this->getDoctrine()->getManager();
        $compagny = $em2->getRepository(Client::class)->findOneBy(array('compagnyName'=> $nameComapagny));
        
        
        if ($compagny){
            
            $idCompagny = $compagny->getId();
            
            $em = $this->getDoctrine()->getManager();
            
            $users = $em->getRepository(User::class)->findBy(array('client_id'=>$idCompagny));
            
            
            if ($users){
                
                $data = $this->get('jms_serializer')->serialize($users,'json');
                
                $reponse = new Response($data);
                $reponse->headers->set('Content-Type', 'application/json');
                
                return $reponse;
            }else{
                
                $error = ['error'=> 'Users not found'];
                
                $data = $this->get('jms_serializer')->serialize($error, 'json');
                
                $reponse = new Response($data);
                
                $reponse->headers->set('Content-Type', 'application/json');
                
                return $reponse;
            }
            
        }else{
            
            $error = ['error'=> 'Client not found'];
            
            $data = $this->get('jms_serializer')->serialize($error, 'json');
            
            $reponse = new Response($data);
            
            $reponse->headers->set('Content-Type', 'application/json');
            
            return $reponse;
            
        }

        }
        /**
         * @Route("api/user", name="createUser")
         * @method({"POST"})
         * @param Request $request
         * @return \Symfony\Component\HttpFoundation\Response
         */
        
        public function User(Request $request){
            
            $data = $request->getContent();
            
            
            $user = $this->get('jms_serializer')->deserialize($data, User::class,'json');
            // manque idclient          
                       
            $em = $this->getDoctrine()->getManager();
            
            $em->persist($user);
            $em->flush();
            
            return new Response('', Response::HTTP_CREATED);
        }
        
        /**
         * @Route("DEL/user/{id}", name="DELuser")
         * @param int $id
         * @return \Symfony\Component\HttpFoundation\Response
         */
        
        public function supUser($id){
            
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(User::class)->find($id);
            
            if($user){
                
                $em->remove($user);
                $em->flush();
                
                return new Response('', Response::HTTP_ACCEPTED);
                
            }else{
                
                return new Response('user not found', Response::HTTP_BAD_REQUEST);
            }
            

            
        }
        /**
         * @Route("GET/user/{id}", name="detail_user")
         * @param string $id
         * @return \Symfony\Component\HttpFoundation\Response
         */
        
        
        public function getUserById($id){
            
            
            $nameComapagny = 'SARL SmoMobile';
            
            $em2 = $this->getDoctrine()->getManager();
            $compagny = $em2->getRepository(Client::class)->findOneBy(array('compagnyName'=> $nameComapagny));
            
            
            if ($compagny){
                
                $idCompagny = $compagny->getId();
                
                $em = $this->getDoctrine()->getManager();
                
                $users = $em->getRepository(User::class)->findOneBy(array(
                    'id'=>$id,
                    'client_id'=> $idCompagny                        
                ));
                
                
                if ($users){
                    
                    $data = $this->get('jms_serializer')->serialize($users,'json');
                    
                    $reponse = new Response($data);
                    $reponse->headers->set('Content-Type', 'application/json');
                    
                    return $reponse;
                    
                }else{
                    
                    $error = ['error'=> 'Users not found'];
                    
                    $data = $this->get('jms_serializer')->serialize($error, 'json');
                    
                    $reponse = new Response($data);
                    
                    $reponse->headers->set('Content-Type', 'application/json');
                    
                    return $reponse;
                }
                
            }else{
                
                $error = ['error'=> 'Client not found'];
                
                $data = $this->get('jms_serializer')->serialize($error, 'json');
                
                $reponse = new Response($data);
                
                $reponse->headers->set('Content-Type', 'application/json');
                
                return $reponse;
                
            }
            
           
        }
        
    /**
     * @route("testclient/", name="testclient")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
        
        public function testclient(){
         
            $clientManager = $this->container->get('fos_oauth_server.client_manager.default');
            $client = $clientmanager->createClient();
            $client->setRedirectUris(array('http://www.exemple.com'));
            $client->setAllowedGrantTypes(array('token', 'authorization_code'));
            $clientManager->updateClient($client);
            
            return $this->redirect($this->generateUrl('fos_oauth_server_authorize', array(
                'client_id'     => $client->getPublicId(),
                'redirect_uri'  => 'http://www.exemple.com',
                'response_type' => 'code'
                
            )));
        }
        

    }
    
    

