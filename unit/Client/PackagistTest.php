<?php
namespace NightWatch\Client;

use GuzzleHttp\Client;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class PackagistTest extends TestCase
{
    private $client;

    private $testedInstance;

    public function setUp()
    {
        $this->client = $this->prophesize(Client::class);

        $this->testedInstance = new Packagist(
            $this->client->reveal()
        );
    }

    public function testConstructor()
    {
        self::assertInstanceOf(Packagist::class, $this->testedInstance);
    }

    public function testGetLatestVersion()
    {
        $result = <<<JSON
        {
          "package":{
            "name":"guzzlehttp\/guzzle",
            "versions":{
              "dev-master":{
                "name":"name",
                "version":"dev-master",
                "version_normalized":"9999999-dev"
              },
              "5.3.x-dev": {
                "name": "name",
                "version": "5.3.x-dev",
                "version_normalized": "5.3.9999999.9999999-dev"
              },
              "6.2.3": {
                "name": "guzzlehttp/guzzle",
                "version": "6.2.3",
                "version_normalized": "6.2.3.0"
              }
            }
          }
        }
JSON;
        $stream = $this->prophesize(StreamInterface::class);
        $stream->getContents()->willReturn($result);

        $response = $this->prophesize(MessageInterface::class);
        $response->getBody()->willReturn($stream->reveal());

        $this->client->get(Argument::type('string'))->willReturn($response->reveal());

        self::assertInternalType('string', $this->testedInstance->getLatestVersion('plop'));
    }
}
