<?php
/*
 * This file is part of pmg/pheanstalk-bundle
 *
 * Copyright (c) PMG <https://www.pmg.com>
 *
 * For full copyright information see the LICENSE file distributed
 * with this source code.
 *
 * @license     http://opensource.org/licenses/Apache-2.0 Apache-2.0
 */

namespace PMG\PheanstalkBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pheanstalk\PheanstalkInterface;
use Pheanstalk\Exception\ServerException;

/**
 * Show the beanstalkd server or tube states.
 *
 * @since    1.0
 */
final class StatsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('pheanstalk:stats');
        $this->setDescription('Show the stats for the Beanstalkd server or a single tube');
        $this->addOption(
            '--connection',
            '-c',
            InputOption::VALUE_REQUIRED,
            'The Pheanstalk connection to use'
        );
        $this->addArgument(
            'tube',
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'The tube(s) for which to show stats'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $in, OutputInterface $out)
    {
        $conn = $this->getConnection($in);

        if ($tubes = $in->getArgument('tube')) {
            foreach (array_unique($tubes) as $tube) {
                $this->writeTubeStats($conn, $tube, $out);
            }
        } else {
            $out->writeln('<info>Server Stats</info>');
            $this->writeStats($conn->stats(), $out);
        }

        return 0;
    }

    private function writeTubeStats(PheanstalkInterface $conn, $tube, OutputInterface $out)
    {
        try {
            $stats = $conn->statsTube($tube);
            $out->writeln("<info>{$tube} Stats</info>");
            $this->writeStats($stats, $out);
        } catch (ServerException $e) {
            if (false !== stripos($e->getMessage(), 'NOT_FOUND')) {
                return $out->writeln(sprintf('<error>Tube "%s" not found</error>', $tube));
            }
            throw $e;
        }
    }

    private function writeStats($stats, OutputInterface $out)
    {
        foreach ($stats as $name => $val) {
            $out->writeln(sprintf('<comment>%s</comment>: %s', $name, $val));
        }
        $out->writeln('');
    }

    private function getConnection(InputInterface $in)
    {
        $container = $this->getContainer();
        $conn = $in->getOption('connection');

        if (!$conn) {
            return $container->get('pmg_pheanstalk');
        }

        $connections = $container->getParameter('pmg_pheanstalk.params.connections');
        if (!isset($connections[$conn])) {
            throw new \InvalidArgumentException(sprintf('No Pheanstalk connection named "%s"', $conn));
        }

        return $container->get($connections[$conn]);
    }
}
