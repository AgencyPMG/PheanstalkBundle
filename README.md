# PmgPheanstalkBundle

An extremely simple Symfony bundle that puts one or more
[Pheanstalk](https://github.com/pda/pheanstalk) connections
into your Symfony application.

If you're looking for something more full featured, check out
[LeezyPheanstalkBundle](https://github.com/armetiz/LeezyPheanstalkBundle).

## Installation

### 1. Download the Bundle

```
composer require pmg/pheanstalk-bundle
```

### 2. Enable the Bundle

```php
<?php
// app/AppKernel

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new PMG\PheanstalkBundle\PmgPheanstalkBundle(),
        ];

        // ...

        return $bundles;
    }

    // ...
}
```

## Configuration

By default the bundle will provide you with one `Pheanstalk\Pheanstalk` named
`pmg_pheanstalk` in the container. This connects to `localhost` on the default
`11300` port.

### Single Connection Example

```yaml
# Default configuration for "PmgPheanstalkBundle"
pmg_pheanstalk:
    # The connection's host.
    host:                 localhost

    # The connection's port.
    port:                 11300

    # The connection's timeout.
    timeout:              null

    # Whether or not to keep the connection's socket around between requests. See http://php.net/manual/en/function.pfsockopen.php
    persist:              false
```

### Multiple Connections

```yaml
# Default configuration for "PmgPheanstalkBundle"
pmg_pheanstalk:
    # The default connection that will be made available at the `pmg_pheanstalk` service
    default_connection:   default
    connections:
        # use the default configuration
        default: ~

        # another connection, will be put at the `pmg_pheanstalk.another` service
        another:
            host: anotherServier.com
            port: 11301
```
