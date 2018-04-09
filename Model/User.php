<?php

namespace Jorijn\SymfonyBunqBundle\Model;

use bunq\Model\Core\BunqModel;
use bunq\Model\Generated\Endpoint\UserCompany;
use bunq\Model\Generated\Endpoint\UserLight;
use bunq\Model\Generated\Endpoint\UserPerson;

class User
{
    /**
     * @var UserCompany|UserLight|UserPerson
     */
    protected $bunqUser;

    public function __construct(BunqModel $user)
    {
        $this->bunqUser = $user;
    }

    /**
     * @return UserCompany|UserLight|UserPerson
     */
    public function getBunqUser()
    {
        return $this->bunqUser;
    }
}
