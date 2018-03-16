<?php

namespace Siwecos;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

/**
 * Class SiwecosCreateConfigCommand
 *
 * @package Siwecos
 */
class SiwecosCreateConfigCommand extends Command
{
    // @todo move and share across commands.
    const DEFAULT_CONF_DIR = '/etc/siwecos';
    const DEFAULT_CONF_FILE = 'siwecos-client.yml';

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('siwecos:config:create')
            ->addOption(
                'dir',
                'd',
                InputArgument::OPTIONAL,
                'Optional path to the config file.',
                self::DEFAULT_CONF_DIR
            )
            ->addOption(
                'file',
                'f',
                InputArgument::OPTIONAL,
                'Optional name of the config file.',
                self::DEFAULT_CONF_FILE
            )
            ->setDescription('Creates the Siwecos Client config file')
            ->setHelp(
                'Creates a Yaml file that contains all needed config for 
                the Siwecos Client. It will be created at /etc/siwecos.
                 Make sure you are allowed to write to that location.'
            );

    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io   = new SymfonyStyle($input, $output);
        $dir  = $input->getOption('dir');
        $file = $input->getOption('file');
        $path = $dir.DIRECTORY_SEPARATOR.$file;

        if (!is_dir($dir)) {
            $io->error(
                sprintf('Please make sure %s exists and is writable.', $dir)
            );

            return;
        }

        $io->title(sprintf('Creating configuration at %s', $path));

        // Ask for the needed config data for our YAML file.

        $url       = $io->ask(
            'Please enter the URL of the Siwecos installation',
            '',
            function ($answer) {
                return $this->requireAnswer($answer);
            }
        );
        $user      = $io->ask(
            'Please enter the HTTP Auth Username of the Siwecos installation',
            '',
            function ($answer) {
                return $this->requireAnswer($answer);
            }
        );
        $password  = $io->ask(
            'Please enter the HTTP Auth Password of the Siwecos installation',
            '',
            function ($answer) {
                return $this->requireAnswer($answer);
            }
        );
        $rulesPath = $io->ask(
            'Please enter the local directory to write the Mod Security rules to',
            '',
            function ($answer) {
                return $this->requireAnswer($answer);
            }
        );

        $config = [
            'url' => $url,
            'user' => $user,
            'password' => $password,
            'rulesPath' => $rulesPath,
        ];

        $io->table(['Url', 'User', 'Password', 'Rules dir'], [$config]);

        if (!$io->confirm('Is the data correct?', false)) {
            $io->note('Canceled');

            return;
        }

        $yaml = Yaml::dump($config);

        if (!file_put_contents($path, $yaml)) {
            $io->error('Could not write config file.');
        }

        $io->success('Done writing config file.');
    }

    /**
     * Simple validator for our config.
     *
     * @param $answer string
     * @return string
     */
    private function requireAnswer($answer)
    {
        if (empty($answer)) {
            throw new \RuntimeException(
                'Please enter a value.'
            );
        }

        return $answer;
    }
}
