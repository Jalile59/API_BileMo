<?php
namespace App\service;

use Psr\Log\LoggerInterface;

class Tools
{
    
    private $logger;
    
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
 
    public function op() {
        return 5;
    }
    
    public function userByToken($token) {
        
        
    }
}

