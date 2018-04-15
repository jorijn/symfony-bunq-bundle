<?php

namespace Jorijn\SymfonyBunqBundle\Component\Command;

use bunq\Context\ApiContext;
use bunq\Context\BunqContext;
use bunq\Exception\BunqException;
use Jorijn\SymfonyBunqBundle\Component\CryptInterface;
use Jorijn\SymfonyBunqBundle\Exception\CryptException;
use Jorijn\SymfonyBunqBundle\Exception\RuntimeException;
use Jorijn\SymfonyBunqBundle\Model\User;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ApiHelper
{
    const ERROR_FILE_NOT_EXISTS = '<error>file %s does not exists, have you initialized your configuration?</error>';
    const PROMPT_PASSWORD = 'Password: ';
    const ERROR_NO_PASSWORD_SUPPLIED = 'no password supplied';
    const ERROR_COULD_NOT_CRYPT = '<error>could not encrypt/decrypt the API configuration: %s</error>';
    const ERROR_API_RESTORE = '<error>could not restore API: %s</error>';
    const ERROR_GENERAL = '<error>unexpected error: %s</error>';
    const ERROR_COULD_NOT_SAVE_ENCRYPTED_CONFIGURATION = 'could not save encrypted configuration';

    /** @var string */
    protected $encyptedConfigurationFile;
    /** @var CryptInterface */
    protected $crypt;

    /**
     * ApiHelper constructor.
     *
     * @param string         $config
     * @param CryptInterface $crypt
     */
    public function __construct(string $config, CryptInterface $crypt)
    {
        $this->encyptedConfigurationFile = $config;
        $this->crypt = $crypt;
    }

    /**
     * @throws \Jorijn\SymfonyBunqBundle\Exception\RuntimeException
     * @throws BunqException
     *
     * @return User
     */
    public function currentUser(): User
    {
        if (BunqContext::getUserContext()->isOnlyUserCompanySet()) {
            return new User(BunqContext::getUserContext()->getUserCompany());
        }

        if (BunqContext::getUserContext()->isOnlyUserPersonSet()) {
            return new User(BunqContext::getUserContext()->getUserPerson());
        }

        throw new RuntimeException('Unexpected user type received.');
    }

    /**
     * @param QuestionHelper  $helper
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return bool|ApiContext
     */
    public function restore(QuestionHelper $helper, InputInterface $input, OutputInterface $output)
    {
        if (!\file_exists($this->encyptedConfigurationFile)) {
            $output->writeln(
                \sprintf(
                    self::ERROR_FILE_NOT_EXISTS,
                    $this->encyptedConfigurationFile
                )
            );

            return false;
        }

        try {
            $password = $this->getPassword($helper, $input, $output);
            $encryptedContents = \file_get_contents($this->encyptedConfigurationFile);
            $json = $this->crypt->decrypt($encryptedContents, $password);

            $apiContext = ApiContext::fromJson($json);
            $apiContext->ensureSessionActive();

            // store it back with updated credentials
            $this->store($apiContext->toJson(), $password);

            BunqContext::loadApiContext($apiContext);

            return $apiContext;
        } catch (CryptException $exception) {
            $output->writeln(sprintf(self::ERROR_COULD_NOT_CRYPT, $exception->getMessage()));
        } catch (BunqException $exception) {
            $output->writeln(sprintf(self::ERROR_API_RESTORE, $exception->getMessage()));
        } catch (\Throwable $exception) {
            $output->writeln(sprintf(self::ERROR_GENERAL, $exception->getMessage()));
        }

        return false;
    }

    /**
     * @param QuestionHelper  $helper
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    protected function getPassword(QuestionHelper $helper, InputInterface $input, OutputInterface $output)
    {
        $question = new Question(self::PROMPT_PASSWORD);
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $question->setNormalizer(function ($value) {
            return $value ? trim($value) : false;
        });

        // the big question
        $password = $helper->ask($input, $output, $question);
        if (false === $password) {
            throw new RuntimeException(self::ERROR_NO_PASSWORD_SUPPLIED);
        }

        return $password;
    }

    /**
     * @param string               $json
     * @param string               $password
     * @param InputInterface|null  $input
     * @param OutputInterface|null $output
     * @param QuestionHelper|null  $helper
     *
     * @return bool
     */
    public function store(
        string $json,
        string $password = null,
        InputInterface $input = null,
        OutputInterface $output = null,
        QuestionHelper $helper = null
    ): bool {
        try {
            if (empty($password)) {
                $password = $this->getPassword($helper, $input, $output);
            }

            $encryptedContents = $this->crypt->encrypt($json, $password);
            if (false === \file_put_contents($this->encyptedConfigurationFile, $encryptedContents)) {
                throw new RuntimeException(self::ERROR_COULD_NOT_SAVE_ENCRYPTED_CONFIGURATION);
            }

            return true;
        } catch (CryptException $exception) {
            $output->writeln(sprintf(self::ERROR_COULD_NOT_CRYPT, $exception->getMessage()));
        } catch (\Throwable $exception) {
            $output->writeln(sprintf(self::ERROR_GENERAL, $exception->getMessage()));
        }

        return false;
    }
}
