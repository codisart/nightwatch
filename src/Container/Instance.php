<?php

namespace NightWatch\Container;

class Instance
{
    private $image;

    public function __construct($image)
    {
        $this->image = 'nightwatch-composer-7.1';

        # try run container
        # if failure build container
        # if failure exception
    }

    public function start()
    {
        $path = realpath(dirname(__DIR__, 2) . '/container/composer/7.1/');

        echo 'Build the container' . "\n";
        echo '====================================' . "\n";

        $this->runCommand(
            sprintf(
                'docker build -t %s %s',
                $this->image,
                $path
            )
        );

        echo 'Run the container' . "\n";
        echo '====================================' . "\n";
        $this->runCommand(
            sprintf(
                'docker run -it --name %s -v $(readlink -f $SSH_AUTH_SOCK):/ssh-agent -e SSH_AUTH_SOCK=/ssh-agent -d %s',
              $this->image,
              $this->image
            )
        );
        # return exception if failure
        return $this;
    }

    public function runScript($package, $branchName)
    {
        echo 'Add ssh keys to known hosts' . "\n";
        echo '====================================' . "\n";
        $this->runCommand(
            sprintf(
                'docker exec -it %s mkdir /root/.ssh',
                $this->image
            )
        );
        $this->runCommand(
            sprintf(
                'docker exec -it %s sh -c "ssh-keyscan -H git.ubeeqo.com >> ~/.ssh/known_hosts"',
                $this->image
            )
        );

        echo 'Clone project in container' . "\n";
        echo '====================================' . "\n";
        $this->runCommand(
            sprintf(
                'docker exec -it %s git clone git@git.ubeeqo.com:ubeeqo/svc-psp.git project',
                $this->image
            )
        );

        echo 'Update composer package' . "\n";
        echo '====================================' . "\n";
        $this->runCommand(
            sprintf(
                'docker exec -it %s sh -c "cd project && php composer.phar -q global require \"hirak/prestissimo:^0.3\""',
                $this->image
            )
        );

        $this->runCommand(
            sprintf(
                'docker exec -it %s sh -c "cd project && php composer.phar update --quiet %s"',
                $this->image,
                $package
            )
        );

        echo 'Commit changes and push branch' . "\n";
        echo '====================================' . "\n";
        $this->runCommand(
            sprintf(
                'docker exec -it %s sh -c "cd project && git checkout -b %s"',
                $this->image,
                $branchName
            )
        );
        $this->runCommand(
            sprintf(
                'docker exec -it %s sh -c "cd project && git commit --all --no-verify -m \"Nightwatch: Update of the %s package\""',
                $this->image,
                $package
            )
        );
        $this->runCommand(
            sprintf(
                'docker exec -it %s sh -c "cd project && git push --no-verify -u origin %s"',
                $this->image,
                $branchName
            )
        );

        $this->runCommand(
            sprintf(
                'docker exec -it %s rm -rf project',
                $this->image
            )
        );
    }

    public function stop()
    {
        $this->runCommand(
            sprintf(
                'docker stop %s',
                $this->image
            )
        );
        $this->runCommand(
            sprintf(
                'docker rm %s',
                $this->image
            )
        );
    }

    private function runCommand($command)
    {
        exec($command, $output, $returnValue);
        if ($returnValue !== 0) {
            throw new \RuntimeException(implode("\r\n", $output));
        }
        return $output;
    }
}
