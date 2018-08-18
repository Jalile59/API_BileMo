<?php
namespace App\service;

use Psr\Log\LoggerInterface;
use App\Entity\AccessToken;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;


class Tools
{
    
    
    private $em;
    
    
    public function __construct( EntityManagerInterface $entity)
    {
       
        $this->em = $entity;
       
    }
 
    public function op() {
        return 5;
    }
    
    public function getUserByToken($token) {
        
        $accessToken = $this->em->getRepository(AccessToken::class)->findOneBy(array('token'=>$token));
        
        $user = $accessToken->getUser();
   
        return $user;
    }
    
    public function getContentToken($request){
        
        $header = $request->server->getHeaders();  //information de la requête avec le token.
        $token = explode(' ', $header['AUTHORIZATION']); // transforme le string en array.
        
        return $token[1]; //return token
    }
    
    public function checkPrivilegeDeleted(User $userCurrent, $token) {
 
        
            
            $user = $this->getUserByToken($token);

            $userParent = $user->getUserParent();
            $userRole = $user->getRoles();
            

            if($userParent == $userCurrent or $userRole[0] == 'ROLE_SUPER_ADMIN'){
                
                return TRUE;
            }else{
                
                return FALSE;
            }
           

        
    }
    
    public function checkUsertExist($id){
        
        if(is_numeric($id)){
            
            $user = $this->em->getRepository(User::class)->find($id);
            
        }else{
            
            $user = $this->em->getRepository(User::class)->findOneBy(array('email'=>$id));
            
        }
        
        if($user){
            
            return true;
        }else{
            
            return false;
        }
        
    }
}

