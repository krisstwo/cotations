<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Command;


use App\Entity\OAuth2\Client;
use App\Service\Hash\HashInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use FOS\OAuthServerBundle\Util\Random;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class HashDataStringCommand extends Command
{

    /**
     * @var HashInterface
     */
    protected $hash;

    /**
     * HashDataStringCommand constructor.
     *
     * @param HashInterface $hash
     */
    public function __construct(HashInterface $hash)
    {
        parent::__construct();
        $this->hash = $hash;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('easy-price:hash')
            ->setDescription('Hashes a data string (querystring format, url-decoded)')
            ->addOption(
                'string',
                null,
                InputOption::VALUE_REQUIRED,
                'The string to be hashed.',
                null
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Hashing the string');


        $dataString = $input->getOption('string');
        $dataHash = $this->hash->hash($dataString);


        // Give the credentials back to the user
        $headers = ['Data string', 'Data hash'];
        $rows    = [
            [$dataString, $dataHash],
        ];

        $io->table($headers, $rows);

        return 0;
    }
}