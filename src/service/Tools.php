<?php
namespace App\service;

use Psr\Log\LoggerInterface;
use App\Entity\AccessToken;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Filesystem\Filesystem;

class Tools
{

    private $em;

    public function __construct(EntityManagerInterface $entity)
    {
        $this->em = $entity;
    }

    public function getUserByToken($token)
    {
        $accessToken = $this->em->getRepository(AccessToken::class)->findOneBy(array(
            'token' => $token
        ));

        $user = $accessToken->getUser();

        return $user;
    }

    public function getContentToken($request)
    {
        $header = $request->server->getHeaders(); // information de la requête avec le token.
        $token = explode(' ', $header['AUTHORIZATION']); // transforme le string en array.

        return $token[1]; // return token
    }

    public function getUrl($request)
    {
        $data = $request->server->all();
        $url = $data['PHP_SELF'];

        return $url;
    }

    public function checkPrivilege(User $userCurrent, $token)
    {
        $userParent = $this->getUserByToken($token);

        $userRole = $userParent->getRoles();

        if ($userParent == $userCurrent->getUserParent() or $userRole[0] == 'ROLE_SUPER_ADMIN') {

            return TRUE;
        } else {

            return FALSE;
        }
    }

    public function checkUsertExist($id)
    {
        if (is_numeric($id)) {

            $user = $this->em->getRepository(User::class)->find($id);
        } else {

            $user = $this->em->getRepository(User::class)->findOneBy(array(
                'email' => $id
            ));
        }

        if ($user) {

            return true;
        } else {

            return false;
        }
    }

    public function getuserByMailOrId($id)
    {
        if (is_numeric($id)) {

            $user = $this->em->getRepository(User::class)->find($id);

            return $user;
        } else {

            $user = $this->em->getRepository(User::class)->findOneBy(array(
                'email' => $id
            ));

            return $user;
        }
    }

    public function incache($id, $objet)
    {
        $cache = new FilesystemCache();

        $cache->set($id, $objet, 3600);
    }

    public function createUser($userName, $password, $mail, $userparent, $role, $fosUser)
    {}

    public function emailornameExist($input_mail, $input_username)
    {
        $error = [
            'mail' => FALSE,
            'name' => FALSE
        ];

        $mail = $this->em->getRepository(User::class)->findOneBy(array(
            'email' => $input_mail
        ));
        $username = $this->em->getRepository(User::class)->findOneBy(array(
            'username' => $input_username
        ));

        if ($mail) {
            $error['mail'] = TRUE;
        }

        if ($username) {
            $error['name'] = TRUE;
        }

        return $error;
    }

    public function updateUser($datajson, User $user)
    {
       

        if (isset($datajson)) {

            if (isset($datajson['username'])) {

                $user->setUsername($datajson['username']);
            }

            if (isset($datajson['email'])) {
                $user->setEmail($datajson['email']);
            }
            
            if(isset($datajson['password'])){
                $user->setPlainPassword($datajson['password']);
        
            }
            
            $this->em->flush();
            
            return TRUE;
    
        }else{
            return FALSE;
        }
        
    }
    
    
    
    
}

