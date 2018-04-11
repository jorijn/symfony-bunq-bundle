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
     * @return Response
     */
    public function callbackAction(Request $request): Response
    {
        /** @var LoggerInterface $logger */
        $logger = $this->get('logger');
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->get('event_dispatcher');

        // MUTATION_RECEIVED
        $content = '{"NotificationUrl":{"target_url":"https:\/\/79b02673.ngrok.io\/bunq\/callback","category":"MUTATION","event_type":"MUTATION_RECEIVED","object":{"Payment":{"id":91840,"created":"2018-04-10 19:30:20.395924","updated":"2018-04-10 19:30:20.395924","monetary_account_id":11312,"amount":{"currency":"EUR","value":"50.00"},"description":"Topup account NL83BUNQ9900122763Coffey","type":"IDEAL","merchant_reference":null,"maturity_date":"2018-04-10","alias":{"iban":"NL83BUNQ9900122763","is_light":false,"display_name":"Trevor Coffey","avatar":{"uuid":"f4af5345-e97e-4e88-8147-94d5086d04e8","image":[{"attachment_public_uuid":"31f28551-d8ad-40f6-9da1-149a5594a31f","height":1024,"width":1024,"content_type":"image\/png"}],"anchor_uuid":null},"label_user":{"uuid":"103f3077-5abd-4682-9fa0-37818126ecbd","display_name":"Coffey","country":"NL","avatar":{"uuid":"a142b6aa-ebfe-4117-adce-30c9b741fb88","image":[{"attachment_public_uuid":"e37651c3-2868-4034-af5c-4a8ab3f827de","height":640,"width":640,"content_type":"image\/png"}],"anchor_uuid":"103f3077-5abd-4682-9fa0-37818126ecbd"},"public_nick_name":"Trevor"},"country":"NL"},"counterparty_alias":{"iban":"NL91ABNA0417164300","is_light":null,"display_name":"Test Data","avatar":{"uuid":"1fb7bf73-44f7-451c-ac2a-56195723c2bd","image":[{"attachment_public_uuid":"ea271640-9a56-4416-a825-01d866b4bcde","height":640,"width":640,"content_type":"image\/jpeg"}],"anchor_uuid":null},"label_user":{"uuid":"103f3077-5abd-4682-9fa0-37818126ecbd","display_name":"Coffey","country":"NL","avatar":{"uuid":"a142b6aa-ebfe-4117-adce-30c9b741fb88","image":[{"attachment_public_uuid":"e37651c3-2868-4034-af5c-4a8ab3f827de","height":640,"width":640,"content_type":"image\/png"}],"anchor_uuid":"103f3077-5abd-4682-9fa0-37818126ecbd"},"public_nick_name":"Trevor"},"country":"NL"},"attachment":[],"geolocation":null,"batch_id":null,"conversation":null,"allow_chat":true,"scheduled_id":null,"address_billing":null,"address_shipping":null,"sub_type":"PAYMENT","status":"SETTLED","request_reference_split_the_bill":[]}}}}';

        // MUTATION RECEIVED 2
//        $content = '{"NotificationUrl":{"target_url":"https:\/\/79b02673.ngrok.io\/bunq\/callback","category":"MUTATION","event_type":"MUTATION_RECEIVED","object":{"Payment":{"id":91848,"created":"2018-04-10 20:17:46.267404","updated":"2018-04-10 20:17:46.267404","monetary_account_id":11331,"amount":{"currency":"EUR","value":"50.00"},"description":"Beschrijving","type":"BUNQ","merchant_reference":null,"maturity_date":"2018-04-10","alias":{"iban":"NL88BUNQ9900123352","is_light":false,"display_name":"Trevor","avatar":{"uuid":"a963fc66-85d3-4d28-8faf-d505b8831aac","image":[{"attachment_public_uuid":"f0fc53ea-079d-488c-9e4f-6090af725417","height":1024,"width":1024,"content_type":"image\/png"}],"anchor_uuid":null},"label_user":{"uuid":"103f3077-5abd-4682-9fa0-37818126ecbd","display_name":"Trevor","country":"NL","avatar":{"uuid":"a142b6aa-ebfe-4117-adce-30c9b741fb88","image":[{"attachment_public_uuid":"e37651c3-2868-4034-af5c-4a8ab3f827de","height":640,"width":640,"content_type":"image\/png"}],"anchor_uuid":"103f3077-5abd-4682-9fa0-37818126ecbd"},"public_nick_name":"Trevor"},"country":"NL"},"counterparty_alias":{"iban":"NL83BUNQ9900122763","is_light":false,"display_name":"T. Coffey","avatar":{"uuid":"f4af5345-e97e-4e88-8147-94d5086d04e8","image":[{"attachment_public_uuid":"31f28551-d8ad-40f6-9da1-149a5594a31f","height":1024,"width":1024,"content_type":"image\/png"}],"anchor_uuid":null},"label_user":{"uuid":"103f3077-5abd-4682-9fa0-37818126ecbd","display_name":"T. Coffey","country":"NL","avatar":{"uuid":"a142b6aa-ebfe-4117-adce-30c9b741fb88","image":[{"attachment_public_uuid":"e37651c3-2868-4034-af5c-4a8ab3f827de","height":640,"width":640,"content_type":"image\/png"}],"anchor_uuid":"103f3077-5abd-4682-9fa0-37818126ecbd"},"public_nick_name":"Trevor"},"country":"NL"},"attachment":[],"geolocation":null,"batch_id":null,"conversation":null,"allow_chat":true,"scheduled_id":null,"address_billing":null,"address_shipping":null,"sub_type":"PAYMENT","status":"SETTLED","request_reference_split_the_bill":[]}}}}';

        // MUTATION CREATED (20)
//        $content = '{"NotificationUrl":{"target_url":"https:\/\/79b02673.ngrok.io\/bunq\/callback","category":"MUTATION","event_type":"MUTATION_CREATED","object":{"Payment":{"id":91849,"created":"2018-04-10 20:22:11.505656","updated":"2018-04-10 20:22:11.505656","monetary_account_id":11331,"amount":{"currency":"EUR","value":"-20.00"},"description":"Beschrijving!","type":"BUNQ","merchant_reference":null,"maturity_date":"2018-04-10","alias":{"iban":"NL88BUNQ9900123352","is_light":false,"display_name":"T. Coffey","avatar":{"uuid":"a963fc66-85d3-4d28-8faf-d505b8831aac","image":[{"attachment_public_uuid":"f0fc53ea-079d-488c-9e4f-6090af725417","height":1024,"width":1024,"content_type":"image\/png"}],"anchor_uuid":null},"label_user":{"uuid":"103f3077-5abd-4682-9fa0-37818126ecbd","display_name":"T. Coffey","country":"NL","avatar":{"uuid":"a142b6aa-ebfe-4117-adce-30c9b741fb88","image":[{"attachment_public_uuid":"e37651c3-2868-4034-af5c-4a8ab3f827de","height":640,"width":640,"content_type":"image\/png"}],"anchor_uuid":"103f3077-5abd-4682-9fa0-37818126ecbd"},"public_nick_name":"Trevor"},"country":"NL"},"counterparty_alias":{"iban":"NL83BUNQ9900122763","is_light":false,"display_name":"Trevor","avatar":{"uuid":"f4af5345-e97e-4e88-8147-94d5086d04e8","image":[{"attachment_public_uuid":"31f28551-d8ad-40f6-9da1-149a5594a31f","height":1024,"width":1024,"content_type":"image\/png"}],"anchor_uuid":null},"label_user":{"uuid":"103f3077-5abd-4682-9fa0-37818126ecbd","display_name":"Trevor","country":"NL","avatar":{"uuid":"a142b6aa-ebfe-4117-adce-30c9b741fb88","image":[{"attachment_public_uuid":"e37651c3-2868-4034-af5c-4a8ab3f827de","height":640,"width":640,"content_type":"image\/png"}],"anchor_uuid":"103f3077-5abd-4682-9fa0-37818126ecbd"},"public_nick_name":"Trevor"},"country":"NL"},"attachment":[],"geolocation":null,"batch_id":null,"conversation":null,"allow_chat":true,"scheduled_id":null,"address_billing":null,"address_shipping":null,"sub_type":"PAYMENT","status":"SETTLED","request_reference_split_the_bill":[]}}}}';

        // MUTATION RECEIVED (20)
//        $content = '{"NotificationUrl":{"target_url":"https:\/\/79b02673.ngrok.io\/bunq\/callback","category":"MUTATION","event_type":"MUTATION_RECEIVED","object":{"Payment":{"id":91850,"created":"2018-04-10 20:22:11.565018","updated":"2018-04-10 20:22:11.565018","monetary_account_id":11312,"amount":{"currency":"EUR","value":"20.00"},"description":"Beschrijving!","type":"BUNQ","merchant_reference":null,"maturity_date":"2018-04-10","alias":{"iban":"NL83BUNQ9900122763","is_light":false,"display_name":"Trevor","avatar":{"uuid":"f4af5345-e97e-4e88-8147-94d5086d04e8","image":[{"attachment_public_uuid":"31f28551-d8ad-40f6-9da1-149a5594a31f","height":1024,"width":1024,"content_type":"image\/png"}],"anchor_uuid":null},"label_user":{"uuid":"103f3077-5abd-4682-9fa0-37818126ecbd","display_name":"Trevor","country":"NL","avatar":{"uuid":"a142b6aa-ebfe-4117-adce-30c9b741fb88","image":[{"attachment_public_uuid":"e37651c3-2868-4034-af5c-4a8ab3f827de","height":640,"width":640,"content_type":"image\/png"}],"anchor_uuid":"103f3077-5abd-4682-9fa0-37818126ecbd"},"public_nick_name":"Trevor"},"country":"NL"},"counterparty_alias":{"iban":"NL88BUNQ9900123352","is_light":false,"display_name":"T. Coffey","avatar":{"uuid":"a963fc66-85d3-4d28-8faf-d505b8831aac","image":[{"attachment_public_uuid":"f0fc53ea-079d-488c-9e4f-6090af725417","height":1024,"width":1024,"content_type":"image\/png"}],"anchor_uuid":null},"label_user":{"uuid":"103f3077-5abd-4682-9fa0-37818126ecbd","display_name":"T. Coffey","country":"NL","avatar":{"uuid":"a142b6aa-ebfe-4117-adce-30c9b741fb88","image":[{"attachment_public_uuid":"e37651c3-2868-4034-af5c-4a8ab3f827de","height":640,"width":640,"content_type":"image\/png"}],"anchor_uuid":"103f3077-5abd-4682-9fa0-37818126ecbd"},"public_nick_name":"Trevor"},"country":"NL"},"attachment":[],"geolocation":null,"batch_id":null,"conversation":null,"allow_chat":true,"scheduled_id":null,"address_billing":null,"address_shipping":null,"sub_type":"PAYMENT","status":"SETTLED","request_reference_split_the_bill":[]}}}}';
//        $content = $request->getContent();

        // decode the request object to see what kind of object this is
        $json = json_decode($content, true);
        if (!\array_key_exists(self::NOTIFICATION_URL, $json)) {
            throw new HttpException(400, self::BAD_REQUEST);
        }

        // serialize into json and feed it to the api components
        $notificationStr = json_encode($json[self::NOTIFICATION_URL]);
        $notification = NotificationUrl::createFromJsonString($notificationStr);

        // if this is not a mutation, don't accept it
        if ($notification->getCategory() !== self::MUTATION) {
            throw new HttpException(405, self::WRONG_REQUEST_TYPE);
        }

        // if the mutation doesn't hold a payment, don't accept it
        $payment = $notification->getObject()->getPayment();
        if ($payment === null) {
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
