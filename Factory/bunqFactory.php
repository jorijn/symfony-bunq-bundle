<?php

namespace Jorijn\SymfonyBunqBundle\Factory;

use bunq\Context\ApiContext;
use bunq\Context\BunqContext;
use Jorijn\SymfonyBunqBundle\Model\User;

class bunqFactory
{
    /**
     * @param string $jsonLocation
     *
     * @throws \bunq\Exception\BunqException
     *
     * @return ApiContext
     */
    public function restoreApi(string $jsonLocation): ApiContext
    {
        // not really happy about this method, but since ApiContext's constructor is private
        // we can't wire it into the symfony container.
        $apiContext = ApiContext::restore($jsonLocation);
        $apiContext->ensureSessionActive();
        $apiContext->save($jsonLocation);

        BunqContext::loadApiContext($apiContext);

        return $apiContext;
    }

    /**
     * @throws \RuntimeException
     * @throws \bunq\Exception\BunqException
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

        throw new \RuntimeException('Unexpected user type received.');
    }
}
