<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\View;
use App\Entity\Client;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Util\UserManipulator;
use App\Entity\AccessToken;



class UserController extends Controller
{
    /**
     * @Get(
     *  path ="/api/user/{id}",
     *  name = "app_user_show"
     * )
     * @View
     */
    
    public function showUser($id) {
        
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($id);
        
        if($user){
            return $user;
        }else{
            
            return new Response('user not found !', RedirectResponse::HTTP_BAD_REQUEST);
        }
        
    }
    
    /**
     * @Put(
     *  path ="/api/user",
     *  name = "app_user_add"
     * )
     */
    
    public function createlUser(Request $request) {
        
        
        
        
        $header = $request->server->getHeaders();  //information de la requete avec le token.
        $token = explode(' ', $header['AUTHORIZATION']); // transforme le string en array.
        
        $em = $this->getDoctrine()->getManager();
        
        // repository
        
        $idAdmin = $em->getRepository(AccessToken::class)->findOneBy(array('token'=> $token[1]));
        $userParent = $idAdmin->getUser();
        
        //dump($idAdmin);
        
       ///////////////récuperation contenu/////////////////////////
        
        $data = $request->getContent();
        $datas = json_decode($data, true);
        
        //////////////// vérification name or email déja utilisé
        
        
        
        $em_mail = $this->getDoctrine()->getManager();
        $mail =$em_mail->getRepository(User::class)->findOneBy(array('email'=>$datas['email']));
        
        $em_name = $this->getDoctrine()->getManager();
        $name =$em_name->getRepository(User::class)->findOneBy(array('username'=>$datas['username']));
        
        if($mail or $mail){
            
            return new Response('Username or Mail is already used.', Response::HTTP_BAD_REQUEST);
        }else{
        
        //////////////// create user //////////////////////////////////////
        
        
        $userManager = $this->get('fos_user.user_manager');
               
        $user = $userManager->createUser(); /* @var $user User */
        
        $user->setUsername($datas['username']);
        $user->setPlainPassword($datas['password']);
        $user->setEmail($datas['email']);
        $user->setEnabled($datas['username']); 
        $user->setUserParent($userParent);
       
        
        
        
        ////////////////// create client ////////////////////////////////
        
            
            $em = $this->getDoctrine()->getManager();
            
            $client = new Client();
            $client->setUserid($user);
            $client->setNumber(25);
            $client->setAllowedGrantTypes(array(
                'password',
                'refresh_token'
            ));
            
            
            
           
            $em->persist($client);
            
            $user->setClient($client);
            $userManager->updateUser($user);
            
            $em->flush();
            
            $message = (new \Swift_Message()) 
            ->setSubject('Inscription API')
            ->setFrom('SnowTrick@hotmail.com')
            ->setTo('jal.djellouli@gmail.com')
            ->setBody('test');
            
           
            
            $this->get('mailer')->send($message);

            
            
       
        return new Response('success new user.', Response::HTTP_ACCEPTED);
        }
    }
    
    /**
     * @Delete(
     *      
     *      path ="/api/deleteUser/{id}",
     *      name = "suppresion_user"
     * )
     * 
     * @param integer $id
     */
    
    public function deluser($id) {
        
       $em = $this->getDoctrine()->getManager();
       $em2 = $this->getDoctrine()->getManager();
       
       $user = $em->getRepository(User::class)->find($id);
       $client = $em2->getRepository(Client::class)->findOneBy(array('userid'=>$user->getId()));
       
       if ($client){
       
           $em->remove($client);
           $em->flush();
           
           return new Response('user removed', Response::HTTP_ACCEPTED);          
           
       }else{
           
           return new Response('User not found', Response::HTTP_BAD_REQUEST);
       }
       

    }
    
    


}
    
    

