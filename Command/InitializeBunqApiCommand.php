<?php

namespace Jorijn\SymfonyBunqBundle\Command;

use bunq\Context\ApiContext;
use bunq\Util\BunqEnumApiEnvironmentType;
use bunq\Util\InstallationUtil;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class InitializeBunqApiCommand extends Command
{
    /**
     * @var string
     */
    protected $environment;
    /**
     * @var string
     */
    protected $configurationFile;
    /**
     * @var string
     */
    private $applicationDescription;
    /**
     * @var array
     */
    private $allowedIps;

    /**
     * InitializeBunqApiCommand constructor.
     *
     * @param string $name
     * @param string $environment
     * @param string $configurationFile
     * @param string $applicationDescription
     * @param array  $allowedIps
     */
    public function __construct(
        string $name,
        string $environment,
        string $configurationFile,
        string $applicationDescription = '',
        array $allowedIps = []
    ) {
        parent::__construct($name);

        $this->environment = $environment;
        $this->configurationFile = $configurationFile;
        $this->applicationDescription = $applicationDescription;
        $this->allowedIps = $allowedIps;
    }

    /**
     * Configures the Command instance.
     */
    protected function configure()
    {
        $this->setDescription('This command will ask you for your API key and install it in the right place.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $asker */
        $asker = $this->getHelper('question');

        // check for existence and possibly abort
        if (\file_exists($this->configurationFile)) {
            $question = new ConfirmationQuestion(
                'It looks like the configuration file already exists, do you want to overwrite? [y/N]: ',
                false
            );

            if (!$asker->ask($input, $output, $question)) {
                return;
            }
        }

        $output->writeln(sprintf('About to generate API context for environment: <info>%s</info>', $this->environment));

        try {
            $environmentType = new BunqEnumApiEnvironmentType($this->environment);
            if (BunqEnumApiEnvironmentType::CHOICE_PRODUCTION === $this->environment) {
                $question = new Question('Please enter your bunq API key: ', null);
                do {
                    $apiKey = $asker->ask($input, $output, $question);
                } while (null === $apiKey);

                // will create production environment context with supplied application description
                $apiContext = ApiContext::create(
                    $environmentType,
                    $apiKey,
                    $this->applicationDescription,
                    $this->allowedIps
                );

                $apiContext->save($this->configurationFile);
            } else {
                // will create sandbox environment context
                InstallationUtil::automaticInstall(
                    $environmentType,
                    $this->configurationFile,
                    null
                );
            }

            $output->writeln(sprintf(
                'Succesfully installed the bunq API key to <info>%s</info>, do not forget to protect this file and to erase your copy/pasted data.',
                $this->configurationFile
            ));
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }
    }
}
