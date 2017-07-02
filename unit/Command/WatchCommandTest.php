<?php
namespace NightWatch\Command;

use NightWatch\Client\Packagist as PackagistClient;
use NightWatch\Client\Factory\Gitlab as GitlabFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

function exec($command, &$output, &$returnValue)
{
    $output = 1;
}

class WatchCommandTest extends TestCase
{
    private $packagistClient;

    private $gitlabClient;

    private $testedInstance;

    public function setUp()
    {
        $this->packagistClient = $this->prophesize(PackagistClient::class);
        $this->gitlabClient = $this->prophesize(GitlabFactory::class);

        $packages = [
            [
                'name' => 'guzzlehttp/guzzle',
                'version' => '6.1.0',
            ]
        ];

        $this->testedInstance = new WatchCommand(
            $this->packagistClient->reveal(),
            $this->gitlabClient->reveal(),
            []
        );
    }

    public function testConstructor()
    {
        self::assertInstanceOf(WatchCommand::class, $this->testedInstance);
    }

    // public function testRun()
    // {
    //     $input = $this->prophesize(InputInterface::class);
    //     $output = $this->prophesize(OutputInterface::class);
    //     $reponse = $this->prophesize(ResponseInterface::class);
    //
    //     $this->client->get(Argument::type('string'))->willReturn($reponse->reveal());
    //
    //     self::assertInternalType('int', $this->testedInstance->run(
    //         $input->reveal(),
    //         $output->reveal()
    //     ));
    // }
}
