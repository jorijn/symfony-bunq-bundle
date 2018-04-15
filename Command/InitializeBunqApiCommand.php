<?php

namespace Jorijn\SymfonyBunqBundle\Command;

use bunq\Context\ApiContext;
use bunq\Util\BunqEnumApiEnvironmentType;
use bunq\Util\InstallationUtil;
use Jorijn\SymfonyBunqBundle\Component\Command\ApiHelper;
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
     * @var ApiHelper
     */
    private $apiHelper;

    /**
     * InitializeBunqApiCommand constructor.
     *
     * @param string    $name
     * @param ApiHelper $apiHelper
     * @param string    $environment
     * @param string    $configurationFile
     * @param string    $applicationDescription
     * @param array     $allowedIps
     */
    public function __construct(
        string $name,
        ApiHelper $apiHelper,
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
        $this->apiHelper = $apiHelper;
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
            $tempfile = tempnam(\sys_get_temp_dir(), 'bunq_');

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

                $apiContext->save($tempfile);
            } else {
                // will create sandbox environment context
                InstallationUtil::automaticInstall(
                    $environmentType,
                    $tempfile,
                    null
                );
            }

            $contents = \file_get_contents($tempfile);

            $output->writeln('Successfully connected to the bunq API, please provide a password to encrypt the configuration file with. Make it strong!');
            $this->apiHelper->store($contents, null, $input, $output, $asker);

            if (false === \unlink($tempfile)) {
                $output->writeln('<error>WARNING: </error> unable to delete temporary installation file, you really should delete this manually: '.$tempfile);
            } else {
                $output->writeln('<info>Success!</info>');
            }
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }
    }
}
