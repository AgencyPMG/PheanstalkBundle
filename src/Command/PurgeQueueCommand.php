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

use PMG\PheanstalkBundle\Service\QueueUtilities;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Purge a single beanstalkd tube of jobs.
 */
#[AsCommand(name: 'pheanstalk:purge-queue')]
final class PurgeQueueCommand extends Command
{
    public function __construct(private QueueUtilities $queueUtilities, $name=null)
    {
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() : void
    {
        $this->setDescription('Show the stats for the Beanstalkd server or a single tube');
        $this->addOption(
            '--connection',
            '-c',
            InputOption::VALUE_REQUIRED,
            'The Pheanstalk connection to use'
        );
        $this->addArgument(
            'queue',
            InputArgument::REQUIRED,
            'The queue to purge'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $in, OutputInterface $out) : int
    {
        $connName = $in->getOption('connection');
        $queue = $in->getArgument('queue');

        $result = $this->queueUtilities->purgeQueue($connName, $queue);

        $out->writeln(sprintf('removed %d jobs from %s', $result, $queue));

        return 0;
    }
}
