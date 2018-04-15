<?php

namespace Jorijn\SymfonyBunqBundle\Command;

use bunq\Exception\BunqException;
use bunq\Model\Generated\Endpoint\UserCompany;
use bunq\Model\Generated\Endpoint\UserPerson;
use bunq\Model\Generated\Object\NotificationFilter;
use Jorijn\SymfonyBunqBundle\Component\Command\ApiHelper;
use Jorijn\SymfonyBunqBundle\Component\Traits\ApiContextAwareTrait;
use Jorijn\SymfonyBunqBundle\Exception\RuntimeException;
use Jorijn\SymfonyBunqBundle\Model\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
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

    /** @var RouterInterface */
    private $router;
    /**
     * @var ApiHelper
     */
    private $apiHelper;

    /**
     * ListCurrentUserCommand constructor.
     *
     * @param string          $name
     * @param ApiHelper       $apiHelper
     * @param RouterInterface $router
     */
    public function __construct(string $name, ApiHelper $apiHelper, RouterInterface $router)
    {
        parent::__construct($name);

        $this->router = $router;
        $this->apiHelper = $apiHelper;
    }

    /**
     * Configures the Command instance.
     */
    protected function configure()
    {
        $this->setDescription('This command will show you current callback url registration and allow you to change that');
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

        $output->writeln(\sprintf('Checking for URL: <info>%s</info>', $this->getUrl()).PHP_EOL);

        /** @var QuestionHelper $asker */
        $asker = $this->getHelper('question');
        $user = $this->apiHelper->currentUser();

        $enabled = $this->getCallbackStatus($user);
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
                $this->disableCallback($user);
            } else {
                $this->enableCallback($user);
            }

            $output->writeln('<info>New status successfully applied.</info>');
        } catch (BunqException $exception) {
            $output->writeln('<error>An error occured while applying new status: '.$exception->getMessage().'</error>');
        }
    }

    /**
     * @return string
     */
    protected function getUrl(): string
    {
        return $this->router->generate(
            self::SYMFONY_BUNQ_CALLBACK_URL,
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    protected function getCallbackStatus(User $user): bool
    {
        $allCurrentNotificationFilter = $user->getBunqUser()->getNotificationFilters();

        /** @var NotificationFilter $filter */
        foreach ($allCurrentNotificationFilter as $filter) {
            if (
                self::NOTIFICATION_CATEGORY_MUTATION === $filter->getCategory()
                && self::NOTIFICATION_DELIVERY_METHOD_URL === $filter->getNotificationDeliveryMethod()
                && $filter->getNotificationTarget() === $this->getUrl()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param User $user
     */
    protected function disableCallback(User $user)
    {
        $callbackUrl = $this->getUrl();

        // get the existing filters with the callback url filtered out
        $allUpdatedNotificationFilter = $this->getExistingFiltersWithoutCurrentCallback($callbackUrl, $user);

        $this->updateNotificationFilters($allUpdatedNotificationFilter);
    }

    /**
     * @param string $callbackUrl
     * @param User   $user
     *
     * @return NotificationFilter[]
     */
    protected function getExistingFiltersWithoutCurrentCallback(string $callbackUrl, User $user): array
    {
        $allCurrentNotificationFilter = $user->getBunqUser()->getNotificationFilters();
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
     *
     * @throws \bunq\Exception\BunqException
     */
    protected function updateNotificationFilters($allUpdatedNotificationFilter = [])
    {
        switch (\get_class($this->apiHelper->currentUser()->getBunqUser())) {
            case UserPerson::class:
                UserPerson::update(
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $allUpdatedNotificationFilter
                );
                break;
            case UserCompany::class:
                UserCompany::update(
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $allUpdatedNotificationFilter
                );
                break;
            default:
                throw new RuntimeException('Unexpected type of user');
                break;
        }
    }

    protected function enableCallback(User $user)
    {
        $callbackUrl = $this->getUrl();

        // get the existing filters to not override the current filters.
        $allUpdatedNotificationFilter = $this->getExistingFiltersWithoutCurrentCallback($callbackUrl, $user);

        $allUpdatedNotificationFilter[] = new NotificationFilter(
            self::NOTIFICATION_DELIVERY_METHOD_URL,
            $callbackUrl,
            self::NOTIFICATION_CATEGORY_MUTATION
        );

        $this->updateNotificationFilters($allUpdatedNotificationFilter);
    }
}
