<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;

use FOS\RestBundle\Controller\Annotations\Get;
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
        
       
        
        $data = $request->getContent();
        $datas = json_decode($data, true);
        
        
        
        //////////////// create user //////////////////////////////////////
        
        $userManager = $this->get('fos_user.user_manager');
               
        $user = $userManager->createUser(); /* @var $user User */
        
        $user->setUsername($datas['username']);
        $user->setPlainPassword($datas['password']);
        $user->setEmail($datas['email']);
        $user->setEnabled($datas['username']); 
        $user->setUserParent($userParent);
       
        
        $userManager->updateUser($user);
        
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
            $em->flush();
            
            $message = (new \Swift_Message()) 
            ->setSubject('Inscription API')
            ->setFrom('SnowTrick@hotmail.com')
            ->setTo('jal.djellouli@gmail.com')
            ->setBody('test');
            
           
            
            $this->get('mailer')->send($message);

            
            
       
        return new Response('okk', Response::HTTP_ACCEPTED);
    }
    
    


}
    
    

