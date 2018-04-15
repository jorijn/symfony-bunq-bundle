<?php

namespace Jorijn\SymfonyBunqBundle\Component;

interface CryptInterface
{
    /**
     * @param string $data
     * @param string $password
     *
     * @return string
     */
    public function encrypt(string $data, string $password): string;

    /**
     * @param string $data
     * @param string $password
     *
     * @return string
     */
    public function decrypt(string $data, string $password): string;
}
