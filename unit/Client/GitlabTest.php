<?php
namespace NightWatch\Client;

use GuzzleHttp\Client;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class GitlabTest extends TestCase
{
    private $client;

    private $testedInstance;

    public function setUp()
    {
        $this->client = $this->prophesize(Client::class);

        $this->testedInstance = new Gitlab(
            $this->client->reveal()
        );
    }

    public function testConstructor()
    {
        self::assertInstanceOf(Gitlab::class, $this->testedInstance);
    }

    public function testGetComposerRequiredPackages()
    {
        $result = <<<JSON
        {
          "packages": [
            {
              "name": "composer/semver",
              "version": "1.4.2"
            },
            {
              "name": "guzzlehttp/guzzle",
              "version": "6.2.3"
            }
          ]
        }
JSON;
        $stream = $this->prophesize(StreamInterface::class);
        $stream->getContents()->willReturn($result);

        $response = $this->prophesize(MessageInterface::class);
        $response->getBody()->willReturn($stream->reveal());

        $this->client->get(Argument::type('string'), Argument::type('array'))->willReturn($response->reveal());

        self::assertInternalType('array', $this->testedInstance->getComposerLockedPackages());
    }

    public function testGetComposerLockedPackages()
    {
        $result = <<<JSON
        {
          "packages": [
            {
              "name": "composer/semver",
              "version": "1.4.2"
            },
            {
              "name": "guzzlehttp/guzzle",
              "version": "6.2.3"
            }
          ]
        }
JSON;
        $stream = $this->prophesize(StreamInterface::class);
        $stream->getContents()->willReturn($result);

        $response = $this->prophesize(MessageInterface::class);
        $response->getBody()->willReturn($stream->reveal());

        $this->client->get(Argument::type('string'), Argument::type('array'))->willReturn($response->reveal())->shouldBeCalled();

        self::assertInternalType('array', $this->testedInstance->getComposerLockedPackages());
    }

    public function testCreatePullRequest()
    {
        $this->client->post(Argument::type('string'), Argument::type('array'))->shouldBeCalled();

        $this->testedInstance->createPullRequest('master');
    }
}
