<?php

namespace Jorijn\SymfonyBunqBundle\Command;

use Jorijn\SymfonyBunqBundle\Model\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCurrentUserCommand extends Command
{
    /** @var User */
    protected $currentUser;

    /**
     * ListCurrentUserCommand constructor.
     *
     * @param string $name
     * @param User   $currentUser
     */
    public function __construct(string $name, User $currentUser)
    {
        parent::__construct($name);

        $this->currentUser = $currentUser;
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
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bunqUser = $this->getCurrentUser()->getBunqUser();

        /** @var Pointer $pointer */
        foreach ($bunqUser->getAlias() as $pointer) {
            $output->writeln(\sprintf('<info>%s</info>: %s', $pointer->getType(), $pointer->getValue()));
        }
    }

    /**
     * @return User
     */
    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }
}
