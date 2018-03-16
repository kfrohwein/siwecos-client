<?php

namespace Siwecos;

use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

/**
 * Class SiwecosClientCommand
 *
 * @package Siwecos
 */
class SiwecosClientCommand extends Command
{
    const DEFAULT_CONF_DIR = '/etc/siwecos/';
    const DEFAULT_CONF_FILE = 'siwecos-client.yml';

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        // @todo Path config should be moved somewhere central.
        $this
            ->setName('siwecos:rules:update')
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
            ->setDescription('Download the latest Mod Security rules.')
            ->setHelp(
                'Downloads the latest Mod Security rules from the Siwecos
                 application, saves them on the locale maschine and restarts 
                 the apache webserver.'
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

        $config = Yaml::parseFile($path);

        $client = new Client();

        $res = $client->request(
            'GET',
            $config['url'],
            [
                'auth' => [$config['user'], $config['password']],
            ]
        );

        $io->text($res->getBody());

    }
}
