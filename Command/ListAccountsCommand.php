<?php

namespace Jorijn\SymfonyBunqBundle\Command;

use bunq\Model\Generated\Endpoint\MonetaryAccount;
use bunq\Model\Generated\Endpoint\MonetaryAccountBank;
use bunq\Model\Generated\Object\Pointer;
use Jorijn\SymfonyBunqBundle\Component\Traits\ApiContextAwareTrait;
use Jorijn\SymfonyBunqBundle\Model\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;

class ListAccountsCommand extends Command
{
    use ApiContextAwareTrait;

    const IBAN = 'IBAN';
    const UNKNOWN = 'UNKNOWN';

    /**
     * ListAccountsCommand constructor.
     *
     * @param string          $name
     * @param User            $user
     * @param RouterInterface $router
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    /**
     * Configures the Command instance.
     */
    protected function configure()
    {
        $this->setDescription('This command will show you all accounts for the current bunq user.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(['ID', 'Description', 'IBAN', 'Balance']);
        $table->setRows(array_map(function (MonetaryAccount $account) {
            $bankAccount = $account->getMonetaryAccountBank();

            return [
                $bankAccount->getId(),
                $bankAccount->getDescription(),
                $this->getIbanForBankAccount($bankAccount),
                $bankAccount->getBalance()->getCurrency().' '.$bankAccount->getBalance()->getValue(),
            ];
        }, MonetaryAccount::listing()->getValue()));

        $table->render();
    }

    /**
     * @param MonetaryAccountBank $bankAccount
     *
     * @return string
     */
    protected function getIbanForBankAccount(MonetaryAccountBank $bankAccount): string
    {
        /** @var Pointer $alias */
        foreach ($bankAccount->getAlias() as $alias) {
            if (self::IBAN === $alias->getType()) {
                return $alias->getValue();
            }
        }

        return self::UNKNOWN;
    }
}
