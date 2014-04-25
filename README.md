# phergie/phergie-irc-plugin-react-eventfilter

[Phergie](http://github.com/phergie/phergie-irc-bot-react/) plugin for limiting processing of incoming events based on event metadata.

<!--[![Build Status](https://secure.travis-ci.org/phergie/phergie-irc-plugin-react-eventfilter.png?branch=master)](http://travis-ci.org/phergie/phergie-irc-plugin-react-eventfilter)-->

## Install

The recommended method of installation is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
        "phergie/phergie-irc-plugin-react-eventfilter": "dev-master"
    }
}
```

See Phergie documentation for more information on
[installing and enabling plugins](https://github.com/phergie/phergie-irc-bot-react/wiki/Usage#plugins).

## Configuration

```php
use Phergie\Irc\Plugin\React\EventFilter;

$connection = new \Phergie\Irc\Connection(array(
    // ...
));

$userMode = new \Phergie\Irc\Plugin\React\UserMode\Plugin;

// Add this instance to the 'plugins' setting value in bot configuration
$eventFilter = new EventFilter\Plugin(array(

    // All configuration is required

    // Analogous to 'plugins' setting in bot configuration, but for this plugin instance
    // All elements must implement \Phergie\Irc\Bot\React\PluginInterface
    'plugins' => array(
        new Foo\Plugin,
        new Bar\Plugin(array(
            // ...
        )),
    ),

    // Must implement \Phergie\Irc\Plugin\React\EventFilter\FilterInterface
    // This combination limits plugin events to those from ops on this connection
    'filter' => new EventFilter\AndFilter(array(
        new EventFilter\ConnectionFilter($connection),
        new EventFilter\UserModeFilter($userMode, array('o')),
    )),

));
```

## Tests

To run the unit test suite:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
cd tests
../vendor/bin/phpunit
```

## License

Released under the BSD License. See `LICENSE`.
