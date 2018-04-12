<?php

namespace Jorijn\SymfonyBunqBundle\Controller;

use bunq\Model\Generated\Object\NotificationUrl;
use Jorijn\SymfonyBunqBundle\Event\MutationEvent;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BunqController extends Controller
{
    const NOTIFICATION_URL = 'NotificationUrl';
    const MUTATION = 'MUTATION';
    const WRONG_REQUEST_TYPE = 'Wrong request type.';
    const BAD_REQUEST = 'Bad request.';
    const PAYMENT_MISSING_FROM_REQUEST = 'Payment missing from request.';
    const RECEIVED_PAYMENT_OBJECT_FROM_BUNQ_FOR_ACCOUNT_ID = 'Received payment object from bunq for account id %s';
    const CALLBACK_OK = 'CALLBACK_OK';

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function callbackAction(Request $request): Response
    {
        /** @var LoggerInterface $logger */
        $logger = $this->get('logger');
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->get('event_dispatcher');

        // get the body from the request
        $content = $request->getContent();

        // decode the request object to see what kind of object this is
        $json = json_decode($content, true) ?? [];
        if (!\array_key_exists(self::NOTIFICATION_URL, $json)) {
            throw new HttpException(400, self::BAD_REQUEST);
        }

        // serialize into json and feed it to the api components
        $notificationStr = json_encode($json[self::NOTIFICATION_URL]);
        $notification = NotificationUrl::createFromJsonString($notificationStr);

        // if this is not a mutation, don't accept it
        if (self::MUTATION !== $notification->getCategory()) {
            throw new HttpException(405, self::WRONG_REQUEST_TYPE);
        }

        // if the mutation doesn't hold a payment, don't accept it
        $payment = $notification->getObject()->getPayment();
        if (null === $payment) {
            throw new HttpException(400, self::PAYMENT_MISSING_FROM_REQUEST);
        }

        // logging for historical reasons
        $logger->info(
            sprintf(
                self::RECEIVED_PAYMENT_OBJECT_FROM_BUNQ_FOR_ACCOUNT_ID,
                $payment->getMonetaryAccountId()
            ),
            [
                'monetaryAccountId' => $payment->getMonetaryAccountId(),
                'amount' => $payment->getAmount()->getValue(),
                'currency' => $payment->getAmount()->getCurrency(),
                'description' => $payment->getDescription(),
                'counter_party_iban' => $payment->getCounterpartyAlias()->getIban(),
                'counter_party_name' => $payment->getCounterpartyAlias()->getDisplayName(),
            ]
        );

        // create the event and dispatch it into the kernel
        $event = new MutationEvent($payment);
        $dispatcher->dispatch(MutationEvent::NAME, $event);

        // tell bunq we're cool
        return new Response(self::CALLBACK_OK, 200);
    }
}
