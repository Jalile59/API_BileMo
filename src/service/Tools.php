<?php
namespace App\service;

use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManager;


class Tools
{
    
    private $logger;
    private $em;
    
    
    public function __construct( EntityManager $entitymanager, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->em = $entitymanager->getEntityManager();
        dump($entitymanager);
    }
 
    public function op() {
        return 5;
    }
    
    public function userByToken($token) {
        
        
        
    }
}

