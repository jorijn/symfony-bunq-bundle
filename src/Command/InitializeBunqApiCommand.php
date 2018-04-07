<?php

namespace Jorijn\SymfonyBunqBundle\Command;

use bunq\Exception\BunqException;
use bunq\Util\BunqEnumApiEnvironmentType;
use bunq\Util\InstallationUtil;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class InitializeBunqApiCommand extends Command
{
    /** @var string */
    protected $environment;
    /** @var string */
    protected $configurationFile;

    /**
     * InitializeBunqApiCommand constructor.
     *
     * @param string $environment
     * @param string $configurationFile
     */
    public function __construct(string $environment, string $configurationFile)
    {
        parent::__construct(null);

        $this->environment = $environment;
        $this->configurationFile = $configurationFile;
    }

    /**
     * Configures the Command instance.
     */
    protected function configure()
    {
        $this->setDescription('This command will ask you for your API key and install it in the right place.');
    }

    /**
     * TODO: extend the InstallationUtil class because they echo error messages and I want to programmatically process them.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $asker */
        $asker = $this->getHelper('question');
        $apiKey = null;

        if (BunqEnumApiEnvironmentType::CHOICE_PRODUCTION === $this->environment) {
            $question = new Question('Please enter your bunq API key: ', null);
            do {
                $apiKey = $asker->ask($input, $output, $question);
            } while (null === $apiKey);
        }

        try {
            InstallationUtil::automaticInstall(
                new BunqEnumApiEnvironmentType($this->environment),
                $this->configurationFile,
                $apiKey
            );

            $output->writeln(sprintf(
                'Succesfully installed the bunq API key to <info>%s</info>, do not forget to protect this file and to erase your copy/pasted data.',
                $this->configurationFile
            ));
        } catch (BunqException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }
    }
}
