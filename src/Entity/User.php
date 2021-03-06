<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;



/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user")
 * @Serializer\ExclusionPolicy("ALL")
 * 
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "app_user_show",
 *          parameters = { "id" = "expr(object.getId())" },
 *      absolute = true
 *      )
 * )
 * 
 * @Hateoas\Relation(
 *      "create",
 *      href = @Hateoas\Route(
 *          "app_user_add",
 *      absolute = true
 *          
 *      )
 * )
 * 
 *
 */
class User extends BaseUser

{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @ORM\OneToMany(targetEntity=User::class, mappedBy ="userParent", cascade={"persist", "remove"})
     * 
     */
    protected $id;
    
    /**
     * 
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="id")
     * 
     * 
     */
    protected $userParent;
    


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function getUserParent() {
        
        return $this->userParent;
    }
    
    public function setUserParent( $iduser) {
        $this->userParent = $iduser;
    }
    
    public function getClient(){
       return $this->client;
    }
    
    public function setClient($client) {
        return $this->client = $client;
    }

}
