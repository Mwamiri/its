<?php

namespace App\Libraries;

use CodeIgniter\Encryption\EncrypterInterface;
use CodeIgniter\Encryption\Exceptions\EncryptionException;
use RuntimeException;

class CredentialVault
{
    private EncrypterInterface $encrypter;

    public function __construct()
    {
        if (trim((string) config('Encryption')->key) === '') {
            throw new RuntimeException('Set encryption.key in .env before using the credential vault.');
        }

        $this->encrypter = service('encrypter');
    }

    public function encrypt(string $secret): string
    {
        return base64_encode($this->encrypter->encrypt($secret));
    }

    public function decrypt(string $ciphertext): string
    {
        $raw = base64_decode($ciphertext, true);
        if ($raw === false) {
            throw new EncryptionException('Stored credential is not valid base64 ciphertext.');
        }

        return $this->encrypter->decrypt($raw);
    }
}
