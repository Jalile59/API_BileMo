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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\service\Tools;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\HttpKernel\HttpCache\ResponseCacheStrategy;

class UserController extends Controller
{
    /**
     * @Get(
     *  path ="/api/user/{id}",
     *  name = "app_user_show"
     * )
     * @View
     */
    
    public function showUser($id, Request $request, Tools $tools) {
        
        $token = $tools->getContentToken($request);
        $user = $tools->getUserByToken($token);
        
        $access = $tools->checkPrivilege($user, $token);
        $cache = new FilesystemCache();
        
        if($access){
            
            if(!$cache->has($id))   //vérification si id objet déja en cache.
            {
                $user = $tools->getuserByMailOrId($id);
                
                $tools->incache($id, $user); //mise en cache de l'objet.
            }else{
                $user = $cache->get($id); //récuperation objet depuis le cache.
                
                
            }
            
            if($user){
                
                return $user;
                
                }else{
                    
                    return new Response('user not found !', RedirectResponse::HTTP_BAD_REQUEST);
                }
                
            }else{
                
                return new Response('access denied.', RedirectResponse::HTTP_UNAUTHORIZED);
                
            }
        

        
    }
    
    /**
     * @Put(
     *  path ="/api/user",
     *  name = "app_user_add"
     * )
     * 
     * 
     * 
     */
    
    public function createlUser(Request $request, Tools $tools) {
        
        $cache = new FilesystemCache();
        
        $header = $request->server->getHeaders();  //information de la requete avec le token.
        $token = explode(' ', $header['AUTHORIZATION']); // transforme le string en array.
        
        $em = $this->getDoctrine()->getManager();
        
        // repository
        
        $idAdmin = $em->getRepository(AccessToken::class)->findOneBy(array('token'=> $token[1]));
        $userParent = $idAdmin->getUser();
        
        
        
       $roles = $userParent->getRoles();
       
      ////////////////Vérification droit de creation////////////
        
       if(!$roles[0] == 'ROLE_ADMIN' OR! $roles[0] == 'ROLE_SUPER_ADMIN'){
            
            return new Response('droit ADMIN manquant', Response::HTTP_UNAUTHORIZED);
        }
        
       ///////////////récuperation contenu/////////////////////////
        
        $data = $request->getContent();
        $datas = json_decode($data, true);
        
        //////////////// vérification name or email déja utilisé
        
        $checkMailandUser = $tools->emailornameExist($datas['email'], $datas['username']);
        
       
        
        if($checkMailandUser['mail'] or $checkMailandUser['name']){
            
            return new Response('Username or Mail is already used.', Response::HTTP_BAD_REQUEST);
        }else{
        
        //////////////// create user //////////////////////////////////////
            
    
            
            if(isset($datas['ROLE']))
            {
                if($roles[0]=='ROLE_SUPER_ADMIN')
                {
                    $role = 'ROLE_ADMIN';
                    
                }else{
                    
                    return new Response('you cannot add role: '.$datas['ROLE'], Response::HTTP_UNAUTHORIZED);
                }
            }else{
                $role = 'ROLE_SIMPLE_USER';
            }
            
        $userManager = $this->get('fos_user.user_manager');
               
        $user = $userManager->createUser(); /* @var $user User */
        
        $user->setUsername($datas['username']);
        $user->setPlainPassword($datas['password']);
        $user->setEmail($datas['email']);
        $user->setEnabled($datas['username']); 
        $user->setUserParent($userParent);
        $user->addRole($role);
        
        ////////////// Vérification asset user //////////////////////////////
        
        $error = $this->get('validator')->validate($user);
        
        if (count($error)){
            
            return new Response($error, Response::HTTP_BAD_REQUEST);
        }
        
   
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
            ->setFrom('API@Bilemo.com')
            ->setTo('jal.djellouli@gmail.com')
            ->setBody($this->renderView('testmail.html.twig',array(
                'clientId'=> $client->getPublicId(),
                'secret'=>$client->getSecret(),
                
            )), 'text/html');
            
            $cache->clear(); //suprimme le cache en cas d'ajout user.

            $this->get('mailer')->send($message);
            
            //ajouter location header
      
        return new Response('success new user.', Response::HTTP_CREATED);
        }
    }
    
    /**
     * @Delete(
     *      
     *      path ="/api/deleteUser/{id}",
     *      name = "suppresion_user"
     * )
     * 
     * 
     */
    
    public function deluser($id, Tools $tools, Request $request) {
        
        $idExist = $tools->checkUsertExist($id); //Vérifie si l'id reçu existe
        $token = $tools->getContentToken($request);
        
   
        if($idExist){
            
            if(is_numeric($id)){    // si id est int requête par findByIdn sinon findOneby 
                
                $em = $this->getDoctrine()->getManager();
                $em2 = $this->getDoctrine()->getManager();
                
                $user = $em->getRepository(User::class)->find($id);
                                
                $client = $em2->getRepository(Client::class)->findOneBy(array('userid'=>$user->getId()));
                
            }else{
                
                $em = $this->getDoctrine()->getManager();
                $em2 = $this->getDoctrine()->getManager();
                
                $user = $em->getRepository(User::class)->findOneBy(array('email'=> $id));
                
                $client = $em2->getRepository(Client::class)->findOneBy(array('userid'=>$user->getId()));
            }
            
        }else{
            
            return new Response('User not found', Response::HTTP_BAD_REQUEST);
        }
        
        // verfier l'user avant de supprimer
        
        
        $userParent = $tools->checkPrivilege($user, $token);
        
        if($userParent){
            
            if ($client){
                
                $cache = new FilesystemCache();
                
                $em->remove($client);
                $em->flush();
                $cache->clear(); //suprimme tous le cas cache en cas de suppression user.
                
                return new Response('user removed', Response::HTTP_ACCEPTED);
                
            }else{
                
                return new Response('User not found', Response::HTTP_BAD_REQUEST);
                
            }
            
        }else{
            
            return new Response('You cannot deleted this user.', Response::HTTP_FORBIDDEN);
        }
            
        }
        
        /**
         * @Put(
         *  path = "/api/updateuser/{iduser}",
         *  name = "updateuser"
         * 
         * )
         * 
         */
        
        public function updateUser(Request $request, Tools $tools, $iduser)
        {
            
            $userCurrent = $tools->getuserByMailOrId($iduser);
            
            
            if(!$userCurrent){
                
                return new Response('user inconnu', Response::HTTP_BAD_REQUEST);
            }
            
            $token = $tools->getContentToken($request);
            
            $access = $tools->checkPrivilege($userCurrent, $token);
            
            if(!$access){
                
                return new Response('You are no access', Response::HTTP_UNAUTHORIZED);
            }
            
           
            $data = $request->getContent();
            
            $data_decodejson = json_decode($data, true);
            
            $update = $tools->updateUser($data_decodejson, $userCurrent);
            
           if ($update)
           {
               return new Response("Mise à jour ok", Response::HTTP_ACCEPTED);
               
           }else{
               return new Response("une erreur est surevenu", Response::HTTP_BAD_REQUEST);
                              
           }
    
        }
        
        /**
         * @Get(
         *  path = "/api/listinguser/{page}",
         *  name = "Listing_User_By_Client",
         *  requirements = {"page" = "\d+"}
         * )
         * @View
         * 
         * 
         * 
         */
        
        public function getListUserByclient(Request $request, Tools $tools, $page) {
            
            $cache = new FilesystemCache();
            $token = $tools->getContentToken($request);
            $user = $tools->getUserByToken($token);
            
            $url = $tools->getUrl($request);    
            
            if($page <= 0)
            {
                return new Response('la page ne peut etre <= 0', Response::HTTP_BAD_REQUEST);
            }
            
            $access = $tools->checkPrivilege($user, $token);
            
            if ($access) {
                
                if(!$cache->has(md5($url))) //vérification si id objet déja en cache.
                {
                    $em = $this->getDoctrine()->getManager();
                    
                    $listeUser = $em->getRepository(User::class)->getAll_pagination($user->getId(),($page-1));
                    
                    $tools->incache(md5($url), $listeUser);  //mise en cache de l'objet.
                    
                    return $listeUser;
                }else{
                    $listeUser =  $cache->get(md5($url)); //récuperation objet depuis le cache.
                    
                    return $listeUser;
                }
                

            }else{
                
                return new Response('You are no access', Response::HTTP_BAD_REQUEST);
                
            }
            

        }
        
        /**
         * @Get(
         *  path ="/api",
         *  name = "All_ressource"
         * )
         * 
         * @View
         * 
         */
        
        public function ressource () {
            
            $ressource =  [
                'ListingUsers'=>'/api/listinguser',
                'CreateUser'=>'/api/user',
                'ShowUser'=> '/api/user/{id}',
                'ListingProduct' => '/api/products',
                'CreateProduct'=>'/api/product',
                'ShowProduct'=>'/api/product/{id}'

            ];
            

            
      
           
            
            return $ressource;
        }
        
        /*
         * 
         * @GET(
         *      path = "/api/resetPassword/{id}",
         *      name = "Reset_password"
         * 
         * )
         * 
         * @View
         */
        
        public function resetPassword($id, Tools $tools){
            
            $user = $tools->getuserByMailOrId($id); /* @var $user User */
            
            if(!$user)
            {
                return new Response('User no found', Response::HTTP_BAD_REQUEST);
            }
            
                                
            $password = $user->getPlainPassword();
            dump($password);
            $mail = $user->getEmail();
            
            ////////////////////////////////////////////
            
            $message = (new \Swift_Message())
            ->setSubject('Password API')
            ->setFrom('API@Bilemo.com')
            ->setTo('jal.djellouli@gmail.com')
            ->setBody($this->renderView('resendpassword.html.twig',array(
                'pass'=> $password,
                
                
            )), 'text/html');
            
            
            $this->get('mailer')->send($message);
            ///////////////////////////////////
            
            
           
            dump($user);
                                
            return new Response('vous allez recevoir un email', Response::HTTP_ACCEPTED);
     }
        /*
         * @Get(
         *  path = "/api/client",
         *  name = "_By_Client",
         *  
         *  
         *  
         * )
         * @view
         */
        
        public function getclient(){
            

            $em = $this->getDoctrine()->getManager();
            
            $client = $em->getRepository(Client::class)->find(68);
            //* @var $client Client */
            
       
            echo 'Client id: '.$client->getPublicId();
            echo ' Secret: '.$client->getSecret();
            
            $tab = array('clientId'=>$client->getPublicId(),
                'secret' => $client->getSecret());
            
            
            return new Response(' ', Response::HTTP_ACCEPTED);
            
            
            

            
        }
       
       
    
    
}