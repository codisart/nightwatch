<?php
namespace NightWatch\Client\Factory;

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use NightWatch\Client\Gitlab as GitlabClient;

final class Gitlab
{
      public function create(
          $baseUrl,
          $projectId,
          $privateToken
      ) {
          return new GitlabClient(
              new Client,
              $baseUrl,
              $projectId,
              $privateToken
          );
      }
}
