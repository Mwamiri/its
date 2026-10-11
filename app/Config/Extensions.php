<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Extensions: add-on packages installed in System > Extensions.
 */
class Extensions extends BaseConfig
{
    /**
     * Publishers whose signed packages are trusted: name => Ed25519 public key (base64).
     * Unsigned packages can still be installed after an explicit warning.
     *
     * @var array<string, string>
     */
    public array $trustedKeys = [
        'Mwamiri Computers Limited' => 'Hb4TWAsn0BcrfNekBaO128/z/INnqcIzc6ToO5kEl8o=',
    ];

    /** Largest package accepted, in megabytes. */
    public int $maxPackageMb = 50;

    /** File types an extension package may contain. */
    public array $allowedExtensions = [
        'php', 'json', 'md', 'txt', 'css', 'js', 'map',
        'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico',
        'woff', 'woff2', 'ttf', 'otf',
        'xlsx', 'csv', 'pdf', 'html', 'sig',
    ];
}
