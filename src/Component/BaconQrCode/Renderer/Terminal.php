<?php

namespace Jorijn\SymfonyBunqBundle\Component\BaconQrCode\Renderer;

use BaconQrCode\Renderer\Text\Plain;

class Terminal extends Plain
{
    /** @var string */
    protected $fullBlock = "\033[40m  \033[0m";
    /** @var string */
    protected $emptyBlock = "\033[47m  \033[0m";
}