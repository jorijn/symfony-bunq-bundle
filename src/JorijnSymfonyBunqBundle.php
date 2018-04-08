<?php

namespace Jorijn\SymfonyBunqBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class JorijnSymfonyBunqBundle extends Bundle
{
    public function getAlias(): string
    {
        return 'symfony_bunq';
    }
}
