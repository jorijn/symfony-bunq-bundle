<?php

namespace Jorijn\SymfonyBunqBundle\Component;

use Jorijn\SymfonyBunqBundle\Exception\CryptException;

class CryptAes256Cbc implements CryptInterface
{
    const CYPHER = 'aes-256-cbc';
    const OPTIONS = 0;

    /**
     * {@inheritdoc}
     *
     * @throws \Jorijn\SymfonyBunqBundle\Exception\CryptException
     */
    public function encrypt(string $data, string $password): string
    {
        $iv = $this->generateInitializationVector();
        $encryptedData = openssl_encrypt($data, self::CYPHER, $password, self::OPTIONS, $iv);
        if (false === $encryptedData) {
            $messages = [];
            while ($message = openssl_error_string()) {
                $messages[] = $message;
            }

            throw new CryptException('openssl_encrypt returned an error: '.implode(', ', $messages));
        }

        return $encryptedData.':'.bin2hex($iv);
    }

    /**
     * @throws \Jorijn\SymfonyBunqBundle\Exception\CryptException
     *
     * @return string
     */
    protected function generateInitializationVector(): string
    {
        $iv = \openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CYPHER), $secure);

        if (false === $secure) {
            throw new CryptException('function `openssl_random_pseudo_bytes` was unable to generate strong random bytes');
        }

        if (false === $iv) {
            throw new CryptException('function `openssl_random_pseudo_bytes` was unable to generate initialization vector');
        }

        return $iv;
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt(string $data, string $password): string
    {
        list($contents, $iv) = explode(':', $data, 2);
        if (!$contents || !$iv) {
            throw new CryptException('was unable to properly detect contents and iv from encrypted string');
        }

        $unencryptedData = openssl_decrypt($contents, self::CYPHER, $password, self::OPTIONS, hex2bin($iv));
        if (false === $unencryptedData) {
            throw new CryptException('unable to decrypt data, check your password');
        }

        return $unencryptedData;
    }
}
