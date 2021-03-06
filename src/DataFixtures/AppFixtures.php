<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\DataFixtures\ORM;


use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Entity\Product;
use App\Entity\Client;
use App\Entity\Users;



class AppFixtures extends Fixture{
    
    public function load(ObjectManager $manager){
        
        $name = [
            1 => 'iphone 5',
            2 => 'iphone 6',
            3 => 'iphone X',
            4 => 'samsung S5',
            5 => 'samsung S7',
            6 => 'samsung S9',
            7 => 'Xioami note 5',
            8 => 'Xioami Mi 2',
            9 => 'nokia Lumia 830',
            10 => 'Asus Zenphone 3'
        ];
        
        $battery = [
            1 => 3000,
            2 => 3500,
            3 => 3500,
            4 => 3500,
            5 => 3500,
            6 => 3500,
            7 => 3500,
            8 =>  3000,
            9 => 2500,
            10 => 3500
        ];
        
        $memory = [
            1 => 16,
            2 => 64,
            3 => 256,
            4 => 32,
            5 => 32,
            6 => 64,
            7 => 64,
            8 => 128,
            9 => 16,
            10 => 32
        ];
        
        $os = [
            1 => 'apple',
            2 => 'apple',
            3 => 'apple',
            4 => 'Android',
            5 => 'Android',
            6 => 'Android',
            7 => 'Android',
            8 => 'Android',
            9 => 'windows phone',
            10 => 'Android'
            
        ];
        
        $photoDef = [
            1 => 8,
            2 => 16,
            3 => 12,
            4 => 16,
            5 => 16,
            6 => 12,
            7 => 12,
            8 => 16,
            9 => 10,
            10 => 13
        ];
        
        $processor = [
            1 => '?',
            2 => 'A8',
            3 => '2,39 Mhz',
            4 => '2,5 Mhz',
            5 => '2,3 Mhz',
            6 => '2,7 Mhz',
            7 => '1,8 Mhz',
            8 => 'N.C',
            9 => '1,2 Mhz',
            10 => '1.25 Mhz'
            
            
        ];
        
        $ram = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
            7 => 0,
            8 => 6,
            9 => 1,
            10 => 3
        ];
        
        
        $sizeName = count($name);
        
        
        for ($i = 1; $i < $sizeName; $i++) {
            
            $product = new Product();
            
            $product->setName($name[$i]);
            $product->setBaterry($battery[$i]);
            $product->setMemory($memory[$i]);
            $product->setOs($os[$i]);
            $product->setPhotoDef($photoDef[$i]);
            $product->setProcessor($processor[$i]);
            $product->setRam($ram[$i]);
            
            $manager->persist($product);
           
            
            
        }
        
        $client = new Client();
        $client->setCompagnyName('SARL SmoMobile');
        $client->setAdress('25 rue de la paix Paris');
        $client->setNumber('01456585');
        
        $manager->persist($client);
        
        
        $user = new Users();
        
        $user->setName('Jhon');
        $user->setSurname('Do');
        $user->setMail('Jhone@gmail.com');
        $user->setMdp('123');
        
        $manager->persist($user);
        
        $manager->flush();
        
        
    }
    
}