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
use Symfony\Component\HttpFoundation\JsonResponse;

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
        
        $usercurrent = $tools->getuserByMailOrId($id);
        
        if(!$usercurrent){
            
            return new JsonResponse('user not found !',400);
            
        }
        
        $access = $tools->checkPrivilege($usercurrent, $token, TRUE);
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
                    return new JsonResponse('user not found !',400);
                    
                }
                
            }else{
                
                
                return new JsonResponse('access denied.',401);
                
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
            
            
            return new JsonResponse('droit ADMIN manquant',401);
        }
        
       ///////////////récuperation contenu/////////////////////////
        
        $data = $request->getContent();
        $datas = json_decode($data, true);
        
        //////////////// vérification name or email déja utilisé
        
        $checkMailandUser = $tools->emailornameExist($datas['email'], $datas['username']);
        
       
        
        if($checkMailandUser['mail'] or $checkMailandUser['name']){
            
            return new JsonResponse('Username or Mail is already used.',400);
            
        }else{
        
        //////////////// create user //////////////////////////////////////
            
    
            
            if(isset($datas['ROLE']))
            {
                if($roles[0]=='ROLE_SUPER_ADMIN')
                {
                    $role = 'ROLE_ADMIN';
                    
                }else{
                    
                    return new JsonResponse('you cannot add role: '.$datas['ROLE'],401);
                    
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
            
            return new JsonResponse($error,400);
            //return new Response($error, Response::HTTP_BAD_REQUEST);
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
        
          return new JsonResponse('success new user.',201);
        
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
            
            return new JsonResponse('User not found',400);
            
        }
        
        // verfier l'user avant de supprimer
        
        
        $userParent = $tools->checkPrivilege($user, $token, TRUE);
        
        if($userParent){
            
            if ($client){
                
                $cache = new FilesystemCache();
                
                $em->remove($client);
                $em->flush();
                $cache->clear(); //suprimme tous le cas cache en cas de suppression user.
                
                return new JsonResponse('user removed',200);
                
            }else{
                
                return new JsonResponse('User not found',400);
                
                
            }
            
        }else{
            
            return new JsonResponse('You cannot deleted this user.',403);
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
                
                return new JsonResponse('user inconnu',400);
                
            }
            
            $token = $tools->getContentToken($request);
            
            $access = $tools->checkPrivilege($userCurrent, $token, TRUE);
            
            if(!$access){
                
                return new JsonResponse('You are no access',401);
                
            }
            
           
            $data = $request->getContent();
            
            $data_decodejson = json_decode($data, true);
            
            $update = $tools->updateUser($data_decodejson, $userCurrent);
            
           if ($update)
           {
               return new JsonResponse('update ok',200);
               
               
           }else{
               return new JsonResponse('error',400);
               
                              
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
                return new JsonResponse('la page ne peut etre =< 0',400);
            }
            
            $access = $tools->checkPrivilege($user, $token, FALSE);
            
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
                
                return new JsonResponse('You are no access',403);
                
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
        
        /**
         * @Get(
         *  path ="/api/resetpassword/{iduser}",
         *  name = "Reset_password"
         * )
         * 
         * @view
         */
        
        public function resetPassword($iduser, Tools $tools){
            
            $user = $tools->getuserByMailOrId($iduser); /* @var $user User */
            
            if(!$user)
            {
                return new JsonResponse('User no found',400);
                
            }
            
                                
            $newpass = uniqid();
            $user->setPlainPassword($newpass);
            
            $mail = $user->getEmail();
            
            //////////////envoi mail//////////////////////
            
            $message = (new \Swift_Message())
            ->setSubject('Password API')
            ->setFrom('API@Bilemo.com')
            ->setTo($mail)
            ->setBody($this->renderView('resendpassword.html.twig',array(
                'pass'=> $newpass,
                
                
            )), 'text/html');
            
            
            $this->get('mailer')->send($message);
            
            return new JsonResponse('mail has been send',200);
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
            
            $client = $em->getRepository(Client::class)->find(68);  /* @var $client Client */
           
           
       
            echo 'Client id: '.$client->getPublicId();
            echo ' Secret: '.$client->getSecret();
            
            $tab = array('clientId'=>$client->getPublicId(),
                'secret' => $client->getSecret());
            
            
            return new Response(' ', Response::HTTP_ACCEPTED);
            
            
            

            
        }
       
       
    
    
}