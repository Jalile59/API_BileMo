<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;


use App\Entity\Client;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;



class BilemoController extends Controller
{
    
    public function getSecureResourceAction()
    {
        # this is it
        if (false === $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }
        
        // [...]
    }
    
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
     * @Route("/api/mobiles", name="mobiles_create")
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
     * @Route("api/ListMobile", name="liste_mobile")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
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
     * @Route("GET/Mobile/{id}", name="detail_mobile")
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    
    public function getmobile($id){
        
        
        
        $em = $this->getDoctrine()->getManager();
        
        $mobile = $em->getRepository(Product::class)->find($id);
        
        if($mobile){
            
            $data = $this->get('serializer')->serialize($mobile, 'json');
            
            $reponse = new Response($data);
            
            $reponse->headers->set('Content-Type', 'application/json');
            
            return $reponse;
            
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
         * @Route("POST/user", name="createUser")
         * @method({"POST"})
         * @param Request $request
         * @return \Symfony\Component\HttpFoundation\Response
         */
        
        public function putUser(Request $request){
            
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
    
    

