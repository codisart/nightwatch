<?php
namespace NightWatch\Client\Factory;

use GuzzleHttp\Client;
use NightWatch\Client\Packagist as PackagistClient;

final class Packagist
{
      public function create()
      {
          return new PackagistClient(
              new Client
          );
      }
}
