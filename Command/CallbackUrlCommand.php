<?php

namespace Jorijn\SymfonyBunqBundle\Command;

use bunq\Exception\BunqException;
use bunq\Model\Generated\Endpoint\UserCompany;
use bunq\Model\Generated\Endpoint\UserLight;
use bunq\Model\Generated\Endpoint\UserPerson;
use bunq\Model\Generated\Object\NotificationFilter;
use Jorijn\SymfonyBunqBundle\Component\Traits\ApiContextAwareTrait;
use Jorijn\SymfonyBunqBundle\Exception\RuntimeException;
use Jorijn\SymfonyBunqBundle\Model\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class CallbackUrlCommand extends Command
{
    const NOTIFICATION_DELIVERY_METHOD_URL = 'URL';
    const NOTIFICATION_CATEGORY_MUTATION = 'MUTATION';
    const SYMFONY_BUNQ_CALLBACK_URL = 'symfony_bunq.callback_url';

    use ApiContextAwareTrait;

    /** @var UserCompany|UserLight|UserPerson */
    protected $user;
    /** @var RouterInterface */
    private $router;

    /**
     * ListCurrentUserCommand constructor.
     *
     * @param string $name
     * @param User $user
     * @param RouterInterface $router
     */
    public function __construct(string $name, User $user, RouterInterface $router)
    {
        parent::__construct($name);

        $this->user = $user->getBunqUser();
        $this->router = $router;
    }

    /**
     * Configures the Command instance.
     */
    protected function configure()
    {
        $this->setDescription('This command will show you current callback url registration and allow you to change that');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(\sprintf('Checking for URL: <info>%s</info>', $this->getUrl()).PHP_EOL);

        /** @var QuestionHelper $asker */
        $asker = $this->getHelper('question');
        $enabled = $this->getCallbackStatus();
        $question = new ConfirmationQuestion(
            sprintf(
                '%s [y/N]: ',
                $enabled
                    ? 'The callback is currently <info>ACTIVE</info>, would you like to disable?'
                    : 'The callback is currently <info>INACTIVE</info>, would you like to enable?'
            ),
            false
        );

        if (!$asker->ask($input, $output, $question)) {
            return;
        }

        try {
            if ($enabled) {
                $this->disableCallback();
            } else {
                $this->enableCallback();
            }

            $output->writeln('<info>New status successfully applied.</info>');
        }
        catch (BunqException $exception) {
            $output->writeln('<error>An error occured while applying new status: '.$exception->getMessage().'</error>');
        }
    }

    protected function getCallbackStatus()
    {
        $allCurrentNotificationFilter = $this->user->getNotificationFilters();

        /** @var NotificationFilter $filter */
        foreach ($allCurrentNotificationFilter as $filter) {
            if (
                $filter->getCategory() === self::NOTIFICATION_CATEGORY_MUTATION
                && $filter->getNotificationDeliveryMethod() === self::NOTIFICATION_DELIVERY_METHOD_URL
                && $filter->getNotificationTarget() === $this->getUrl()
            ) {
                return true;
            }
        }

        return false;
    }

    protected function getUrl(): string
    {
        return $this->router->generate(
            self::SYMFONY_BUNQ_CALLBACK_URL,
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    protected function disableCallback()
    {
        $callbackUrl = $this->getUrl();

        // get the existing filters with the callback url filtered out
        $allUpdatedNotificationFilter = $this->getExistingFiltersWithoutCurrentCallback($callbackUrl);

        $this->updateNotificationFilters($allUpdatedNotificationFilter);
    }

    /**
     * @param $callbackUrl
     * @return NotificationFilter[]
     */
    protected function getExistingFiltersWithoutCurrentCallback(string $callbackUrl): array
    {
        $allCurrentNotificationFilter = $this->user->getNotificationFilters();
        $allUpdatedNotificationFilter = [];

        foreach ($allCurrentNotificationFilter as $notificationFilter) {
            if ($notificationFilter->getNotificationTarget() !== $callbackUrl) {
                $allUpdatedNotificationFilter[] = $notificationFilter;
            }
        }
        return $allUpdatedNotificationFilter;
    }

    /**
     * @param NotificationFilter[] $allUpdatedNotificationFilter
     */
    protected function updateNotificationFilters($allUpdatedNotificationFilter = [])
    {
        switch (\get_class($this->user)) {
            case UserPerson::class:
                UserPerson::update(
                    null, null, null, null, null, null, null, null, null, null,
                    null, null, null, null, null, null, null, null, null, null,
                    null, null, null, null, null, null, null,
                    $allUpdatedNotificationFilter
                );
                break;
            case UserCompany::class:
                UserCompany::update(
                    null, null, null, null, null, null, null, null, null, null,
                    null, null, null, null, null,
                    $allUpdatedNotificationFilter
                );
                break;
            default:
                throw new RuntimeException('Unexpected type of user');
                break;
        }
    }

    protected function enableCallback()
    {
        $callbackUrl = $this->getUrl();

        // get the existing filters to not override the current filters.
        $allUpdatedNotificationFilter = $this->getExistingFiltersWithoutCurrentCallback($callbackUrl);

        $allUpdatedNotificationFilter[] = new NotificationFilter(
            self::NOTIFICATION_DELIVERY_METHOD_URL,
            $callbackUrl,
            self::NOTIFICATION_CATEGORY_MUTATION
        );

        $this->updateNotificationFilters($allUpdatedNotificationFilter);
    }
}
