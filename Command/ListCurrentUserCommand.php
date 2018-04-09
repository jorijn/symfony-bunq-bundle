<?php

namespace Jorijn\SymfonyBunqBundle\Command;

use bunq\Model\Generated\Endpoint\UserCompany;
use bunq\Model\Generated\Endpoint\UserLight;
use bunq\Model\Generated\Endpoint\UserPerson;
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
     * @return UserCompany|UserLight|UserPerson
     */
    public function getCurrentUser()
    {
        return $this->currentUser;
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
        dump($this->currentUser);
    }
}
