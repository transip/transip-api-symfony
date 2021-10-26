<a href="https://transip.eu" target="_blank">
    <img width="200px" src="https://www.transip.nl/img/cp/transip-logo.svg">
</a>

# TransIP RestAPI bundle for Symfony

This bundle provides an instance of `\Transip\Api\Library\TransipAPI` to Symfony's Container. This bundle is currently 
still in development.

## Requirements

The TransIP RestAPI bundle for Symfony requires the following in order to work properly:

* PHP >=7.4.
* [json](https://www.php.net/manual/en/book.json.php) (php extension)
* [openssl](https://www.php.net/manual/en/book.openssl.php) (php extension)

## Composer
You can install the RestAPI library using [Composer](http://getcomposer.org/). Run the following command:
```bash
composer require transip/transip-api-symfony
```

Then register your bundle in Symfony
```diff
<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
+    Transip\Bundle\RestApi\TransipApiBundle::class => ['all' => true],
];

```

## Getting started
```php
<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class LuckyController
{
    public function number(
        \Transip\Api\Library\TransipAPI $apiClient
    ): Response
    {
       // Authenticate client with Token
       $apiClient->setToken('some.jwt.token');
       
       // Get all VPSes for account #1
       $apiClient->vps()->getAll();
       
       // Request Token with username and private key
       $token = $apiClient->auth()->createToken(
            $transipUsername,
            $transipPrivateKey,
            false, // Create IP Whitelisted tokens
            false, // Create a read only token
            '' // Add Token label
            '1 day' // Create token expire
        );
        // Set token in library
        $apiClient->setToken($token);
        
        // Get all VPSes for account #2
       $apiClient->vps()->getAll();
    }
}
```

For more information about using the TransIP API Client, please look [at its documentation](https://github.com/transip/transip-api-php#get-all-domains)
