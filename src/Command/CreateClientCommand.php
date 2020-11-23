<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Command;


use App\Entity\OAuth2\Client;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateClientCommand extends \FOS\OAuthServerBundle\Command\CreateClientCommand
{
    private $clientManager;

    public function __construct(ClientManagerInterface $clientManager)
    {
        parent::__construct($clientManager);

        $this->clientManager = $clientManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('easy-price:oauth-server:create-client')
            ->setDescription('Creates a new OAuth2 client (with label)')
            ->addOption(
                'label',
                null,
                InputOption::VALUE_REQUIRED,
                'Sets a label for the client.',
                null
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Client Credentials');

        // Create a new client
        /**
         * @var $client Client
         */
        $client = $this->clientManager->createClient();

        $client->setLabel($input->getOption('label'));
        $client->setRedirectUris($input->getOption('redirect-uri'));
        $client->setAllowedGrantTypes($input->getOption('grant-type'));

        // Save the client
        $this->clientManager->updateClient($client);

        // Give the credentials back to the user
        $headers = ['Client ID', 'Client Secret'];
        $rows = [
            [$client->getPublicId(), $client->getSecret()],
        ];

        $io->table($headers, $rows);

        return 0;
    }
}