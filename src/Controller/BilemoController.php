<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Users;

use App\Entity\Client_Compagny;



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
     * @Route("/Mobile/{id}", name="detail_mobile")
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
     * @Route("/listeUsers/{nameComapagny}", name="listeUsersByCompany")
     * @param string nameComapagny
     * @return \Symfony\Component\HttpFoundation\Response
     */
    
    public function getUserByNameCompany($nameComapagny){
        
        
        
       
        $em2 = $this->getDoctrine()->getManager();
        $compagny = $em2->getRepository(Client_Compagny::class)->findOneBy(array('compagnyName'=> $nameComapagny));
        
        
        if ($compagny){
            
            $idCompagny = $compagny->getId();
            
            $em = $this->getDoctrine()->getManager();
            
            $users = $em->getRepository(Users::class)->findBy(array('client_id'=>$idCompagny));
            
            
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
         * @Route("/createUser", name="createUser")
         * @method({"POST"})
         * @param Request $request
         * @return \Symfony\Component\HttpFoundation\Response
         */
        
        public function putUser(Request $request){
            
            $data = $request->getContent();
            
            
            $user = $this->get('jms_serializer')->deserialize($data, Users::class,'json');
            // manque idclient          
                       
            $em = $this->getDoctrine()->getManager();
            
            $em->persist($user);
            $em->flush();
            
            return new Response('', Response::HTTP_CREATED);
        }
        
        /**
         * @Route("/deluser/{id}")
         * @param int $id
         * @return \Symfony\Component\HttpFoundation\Response
         */
        
        public function supUser($id){
            
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(Users::class)->find($id);
            
            if($user){
                
                $em->remove($user);
                $em->flush();
                
                return new Response('', Response::HTTP_ACCEPTED);
                
            }else{
                
                return new Response('user not found', Response::HTTP_BAD_REQUEST);
            }
            

            
        }
        /**
         * @Route("/user/{id}")
         * @param string $id
         * @return \Symfony\Component\HttpFoundation\Response
         */
        
        
        public function getUserById($id){
            
            
            $nameComapagny = 'SARL SmoMobile';
            
            $em2 = $this->getDoctrine()->getManager();
            $compagny = $em2->getRepository(Client_Compagny::class)->findOneBy(array('compagnyName'=> $nameComapagny));
            
            
            if ($compagny){
                
                $idCompagny = $compagny->getId();
                
                $em = $this->getDoctrine()->getManager();
                
                $users = $em->getRepository(Users::class)->findOneBy(array(
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
    }
    
    

