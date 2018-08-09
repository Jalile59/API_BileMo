<?php
namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use FOS\OAuthServerBundle\Entity\ClientManager;

class OAuthClientCreateCommand extends ContainerAwareCommand
{
    
    protected static $defaultName = 'oauth-server:client-create';
    
    private $client_manager;
    
    public function __construct(ClientManager $client_manager)
    {
        parent::__construct();
        $this->client_manager = $client_manager;
    }

    protected function configure()
    {
        $this->setName('oauth-server:client-create')
            ->setDescription('Create a new client')
            ->addArgument('email', InputArgument::REQUIRED, 'Votre addresse email.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        $clientManager = $this->client_manager;
            
        $client = $clientManager->createClient();
        $client->setRedirectUris(array(
            $this->getContainer()
                ->get('kernel')
                ->getRootDir()
        ));
        $client->setAllowedGrantTypes(array(
            'password',
            'refresh_token'
        ));
        $clientManager->updateClient($client);
        
        /*
        $message = \Swift_Message::newInstance()->setSubject('Your Bilemo API credentials')
            ->setFrom('noreply@test.com')
            ->setTo($input->getArgument('email'))
            ->setBody($this->getContainer()
            ->get('templating')
            ->render('Emails/new-oauth-client.html.twig', array(
                'client_id' => $client->getPublicId(),
                'client_secret' => $client->getSecret()
            )), 'text/html');
        $this->getContainer()
            ->get('mailer');
            //->send($message);
             
             */
        $output->writeln('Congrats ! You\'ve been emailed your API credentials.');
        $output->writeln('client_id ' . $client->getPublicId());
        $output->writeln('client_secret ' . $client->getSecret());
        
    }
}
