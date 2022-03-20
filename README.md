<a href="https://transip.eu" target="_blank">
    <img width="200px" src="https://www.transip.nl/img/cp/transip-logo.svg">
</a>

# TransIP RestAPI bundle for Symfony

This bundle provides an instance of `\Transip\Api\Library\TransipAPI` to Symfony's Container. 

## Requirements

The TransIP RestAPI bundle for Symfony requires the following in order to work properly:

* PHP >= 8.0
* [json](https://www.php.net/manual/en/book.json.php) (php extension)
* [openssl](https://www.php.net/manual/en/book.openssl.php) (php extension)

## Installation
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
+   Transip\Bundle\RestApi\TransipApiBundle::class => ['all' => true],
];

```

Lastly create a config file:
```yaml
# config/packages/transip.yaml
transip_api:
  options:
    generateWhitelistOnlyTokens: true
    authentication:
      username: '%env(TRANSIP_USERNAME)%' # The username you use to login onto the Control Panel
      privateKey: '%env(TRANSIP_PRIVATE_KEY)%' # Your Private Key create from the Control Panel
````

## Getting started
```php
<?php
// src/Controller/TransIPApiController.php
namespace Ods\Controller;

use Symfony\Component\HttpFoundation\Response;
use Transip\Api\Library\TransipAPI;
class TransIPApiController
{
    public function getVpses(
        TransipAPI $apiClient
    ): Response {
       // Get all VPSes for account #0 (authentication in config)
       $apiClient->vps()->getAll();
       
       // Authenticate client with Token (account #1)
       $apiClient->setToken('some.jwt.token');
       
       // Get all VPSes for account #1
       $apiClient->vps()->getAll();
       
       // Request Token with username and private key (account #2)
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

## Use client
For more information about using the TransIP API Client, please look [at its documentation](https://github.com/transip/transip-api-php#get-all-domains)
