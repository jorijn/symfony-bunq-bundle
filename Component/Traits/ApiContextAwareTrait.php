<?php

namespace Jorijn\SymfonyBunqBundle\Component\Traits;

use bunq\Context\ApiContext;

trait ApiContextAwareTrait
{
    /** @var ApiContext */
    protected $apiContext;

    /**
     * @return ApiContext
     */
    public function getApiContext(): ApiContext
    {
        return $this->apiContext;
    }

    /**
     * @param ApiContext $apiContext
     */
    public function setApiContext(ApiContext $apiContext)
    {
        $this->apiContext = $apiContext;
    }
}
