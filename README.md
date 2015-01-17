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
new \Phergie\Irc\Plugin\React\EventFilter\Plugin(array(

    // All configuration is required

    // Analogous to 'plugins' setting in bot configuration
    // All elements must implement \Phergie\Irc\Bot\React\PluginInterface
    'plugins' => array(
        // ...
    ),

    // Must reference an object that implements
    // \Phergie\Irc\Plugin\React\EventFilter\FilterInterface
    'filter' => new FooFilter,

))
```

The `'plugins'` setting specifies a list of one or more plugins for which event
processing will be limited.

The `'filter'` setting specifies an object that determines which events will
be forwarded to the plugins referenced by the `'plugins'` setting.

## Usage

This is an example bot configuration that includes the EventFilter plugin:

```php
use Phergie\Irc\Connection;
use Phergie\Irc\Plugin\React\AutoJoin\Plugin as AutoJoinPlugin;
use Phergie\Irc\Plugin\React\EventFilter as Filters;
use Phergie\Irc\Plugin\React\EventFilter\Plugin as EventFilterPlugin;
use Phergie\Irc\Plugin\React\JoinPart\Plugin as JoinPartPlugin;
use Phergie\Irc\Plugin\React\Pong\Plugin as PongPlugin;
use Phergie\Irc\Plugin\React\Quit\Plugin as QuitPlugin;
use Phergie\Irc\Plugin\React\UserMode\Plugin as UserModePlugin;

// These objects are instantiated and assigned to variables here because they
// are referenced multiple times later in the configuration array.
$connection1 = new Connection(array(
    // ...
));
$userModePlugin = new UserModePlugin;

return array(

    'connections' => array(

        $connection1,

        new Connection(array(
            // ...
        ))

    ),

    'plugins' => array(

        // These plugins apply to all connections.
        new PongPlugin,
        $userModePlugin,

        // Because of the applied ConnectionFilter, the bot will automatically
        // join #channel1 only on $connection1.
        new EventFilterPlugin(array(
            'filter' => new Filters\ConnectionFilter(array($connection1)),
            'plugins' => array(
                new AutoJoinPlugin(array('channels' => '#channel1')),
            ),
        )),

        // Because of the applied UserModeFilter, in order to request that the
        // bot join or part a channel, the requesting user must have the op
        // mode in that channel.
        new EventFilterPlugin(array(
            'filter' => new Filters\UserModeFilter($userModePlugin, array('o')),
            'plugins' => array(
                new JoinPartPlugin,
            ),
        )),

        // Because of the applied UserFilter, only the user with the specified
        // user mask will be able to request that the bot terminate its
        // connection to a server.
        new EventFilterPlugin(array(
            'filter' => new Filters\UserFilter(array('nick1!user1@host1')),
            'plugins' => array(
                new QuitPlugin,
            ),
        )),

    ),

);
```

## Supported Filters

All filters supported by this plugin are under the
`\Phergie\Irc\Plugin\React\EventFilter` namespace.

### Metadata Filters

These filters are based on metadata for incoming events.

#### ChannelFilter

Allows events that are either not channel-specific or originate from one of a
specified list of channels.

```php
new ChannelFilter(array(

    '#channel1',
    '&channel2',
    // ...

))
```

#### ConnectionFilter

Allows events that originate from one of a specified list of connections
represented by objects that implement `\Phergie\Irc\ConnectionInterface`.

```php
new ConnectionFilter(array(

    new \Phergie\Irc\Connection(array(
        // ...
    )),

    // ...
))
```

#### UserFilter

Allows events that are either not user-specific or originate from one of a
specified list of users identified by strings containing
[user masks](http://www.ircbeginner.com/opvinfo/masks.html).

```php
new UserFilter(array(

    'nick1!username1@host1',
    'nick2!username2@host2',
    // ...

))
```

#### UserModeFilter

Allows events that are either not user-specific or originate from users with
any of a specified list of modes within the channel in which the events occur.
This mode information is obtained using the
[UserMode plugin](https://github.com/phergie/phergie-irc-plugin-react-usermode).

```php
new UserModeFilter(

    // Pre-configured instance of \Phergie\Irc\Plugin\React\UserMode\Plugin
    $userMode,

    // List of letters corresponding to user modes for which to allow events
    array('o', 'v')

)
```

Common mode values:

* q - owner
* a - admin
* o - op
* h - halfop
* v - voice

### Boolean Filters

These filters are applied to other filters to combine or change their results
in some way.

#### AndFilter

Allows events that pass all contained filters, equivalent to the boolean "and"
operator.

```php
new AndFilter(array(
    new ConnectionFilter(array($connection)),
    new ChannelFilter(array('#channel1', '&channel2')),
))
```

This example allows events that occur both within the specified channels and on
the specified connection.

#### OrFilter

Allows events that pass any contained filters, equivalent to the boolean "or"
operator.

```php
new AndFilter(array(
    new UserFilter(array('nick1!user1@host1')),
    new UserModeFilter($userModePlugin, array('o')),
))
```

This example allows events that are initiated by a user either identified by
the specified user mask or with the op user mode.

#### NotFilter

Allows events that do not pass the contained filter, equivalent to the boolean
"not" operator.

```php
new NotFilter(
    new UserFilter(array('nick1!user1@host1'))
)
```

This example allows events that do not originate from the user identified by
the specified user mask, effectively functioning as a ban list with respect to
functionality of the EventFilter plugin's contained plugins.

## Custom Filters

Filters are merely classes that implement
[`FilterInterface`](https://github.com/phergie/phergie-irc-plugin-react-eventfilter/blob/master/src/FilterInterface.php).
This interface has a single method, `filter()`, which accepts an event object
that implements [`EventInterface`](https://github.com/phergie/phergie-irc-event/blob/master/src/EventInterface.php)
as its only parameter and returns `true` if the event should be allowed or
`false` if it should not.

## Tests

To run the unit test suite:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
./vendor/bin/phpunit
```

## License

Released under the BSD License. See `LICENSE`.
