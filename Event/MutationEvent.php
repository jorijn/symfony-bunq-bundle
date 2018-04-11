<?php

namespace Jorijn\SymfonyBunqBundle\Event;

use bunq\Model\Generated\Endpoint\Payment;
use Symfony\Component\EventDispatcher\Event;

class MutationEvent extends Event
{
    const NAME = 'symfony_bunq.event.mutation';

    /** @var Payment */
    protected $payment;

    /**
     * MutationEvent constructor.
     *
     * @param Payment $payment
     */
    public function __construct(Payment $payment)
    {
        $this->setPayment($payment);
    }

    /**
     * @return Payment
     */
    public function getPayment(): Payment
    {
        return $this->payment;
    }

    /**
     * @param Payment $payment
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
    }
}
