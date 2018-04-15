<?php

namespace Jorijn\SymfonyBunqBundle\Command;

use bunq\Model\Generated\Object\Pointer;
use Jorijn\SymfonyBunqBundle\Component\Command\ApiHelper;
use Jorijn\SymfonyBunqBundle\Model\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCurrentUserCommand extends Command
{
    /** @var ApiHelper */
    protected $apiHelper;

    /**
     * ListCurrentUserCommand constructor.
     *
     * @param string    $name
     * @param ApiHelper $apiHelper
     */
    public function __construct(string $name, ApiHelper $apiHelper)
    {
        parent::__construct($name);

        $this->apiHelper = $apiHelper;
    }

    /**
     * Configures the Command instance.
     */
    protected function configure()
    {
        $this->setDescription('This command will show you the current user which is connect for bunq');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \bunq\Exception\BunqException
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->apiHelper->restore($this->getHelper('question'), $input, $output)) {
            return;
        }

        $bunqUser = $this->apiHelper->currentUser()->getBunqUser();

        /** @var Pointer $pointer */
        foreach ($bunqUser->getAlias() as $pointer) {
            $output->writeln(\sprintf('<info>%s</info>: %s', $pointer->getType(), $pointer->getValue()));
        }
    }
}
