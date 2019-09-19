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

namespace PMG\PheanstalkBundle;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use LogicException;
use Pheanstalk\Contract\PheanstalkInterface;

/**
 * Container for mapping connection name => Pheanstalk interface
 */
class ConnectionManager implements Countable, IteratorAggregate
{
    /**
     * @var PheanstalkInterface[]
     */
    private $connections;

    /**
     * The default connection name.
     *
     * @var string
     */
    private $defaultConnectionName;

    public function __construct(array $connections, string $defaultConnectionName)
    {
        $this->connections = $connections;
        $this->defaultConnectionName = $defaultConnectionName;
    }

    /**
     * Get a connection by its name.
     *
     * @throws LogicException if $name is not set
     * @param $name the connection name
     */
    public function get(string $name) : PheanstalkInterface
    {
        if (!$this->has($name)) {
            throw new LogicException(sprintf('No Pheanstalk connection named "%s"', $name));
        }

        return $this->connections[$name];
    }

    /**
     * Check for the existence of a connection
     */
    public function has(string $name) : bool
    {
        return isset($this->connections[$name]);
    }

    public function getDefault() : PheanstalkInterface
    {
        return $this->get($this->defaultConnectionName);
    }

    /**
     * {@inheritdoc}
     */
    public function count() : int
    {
        return count($this->connections);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->connections);
    }
}
