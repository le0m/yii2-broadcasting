Yii2-broadcasting
=================

[![Latest Stable Version](https://poser.pugx.org/le0m/yii2-broadcasting/version)](https://packagist.org/packages/le0m/yii2-broadcasting)
[![License](https://poser.pugx.org/le0m/yii2-broadcasting/license)](https://packagist.org/packages/le0m/yii2-broadcasting)
[![Monthly Downloads](https://poser.pugx.org/le0m/yii2-broadcasting/d/monthly)](https://packagist.org/packages/le0m/yii2-broadcasting)
[![Total Downloads](https://poser.pugx.org/le0m/yii2-broadcasting/downloads)](https://packagist.org/packages/le0m/yii2-broadcasting)

This component a continuation of [MKiselev/yii2-broadcasting](https://github.com/MKiselev/yii2-broadcasting). It has been re-organized and udpated to work with Yii2 2.0.16.

You can use it to handle notifications through a websocket.

Requirements
------------

- a working instance of [laravel-echo-server](https://github.com/tlaverdure/laravel-echo-server), to handle the Socket.io server and channel authentication
- [yiisoft/yii2-redis](https://github.com/yiisoft/yii2-redis) is installed by this package, for sharing messages with Socket.io server through Redis
- [Laravel Echo](https://github.com/laravel/echo) and [socket.io-client](https://github.com/socketio/socket.io-client) on the frontend, to open the websocket and listen for notifications

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist le0m/yii2-broadcasting:"~0.1"
```

or add

```
"le0m/yii2-broadcasting": "~0.1."
```

to the require section of your composer.json.

Configuration
-------------

Configure the component to use a broadcaster:

```php
'modules' => [
    // configure a redis connection
    'redis' => [
        'class' => 'yii\redis\Connection',
        'hostname' => 'localhost',
        'port' => 6379,
        'database' => 0,
    ],
    'broadcasting' => [
        'class' => 'le0m\broadcasting\BroadcastManager',
        'broadcaster' => [
            'class' => 'le0m\broadcasting\broadcasters\RedisBroadcaster',
            // use the redis connection component (default) or define a new one
            'redis' => 'redis',
            'channels' => [
                // authorization callback for private and presence channels
                'comments.{postId}' => function (yii\web\User $user, $postId) {
                    // use basic roles or RBAC
                    return $user->can('doSomething', ['post' => $postId]);
                },
            ],
        ],
    ]
]
```

There are several broadcast tools available for your choice:

1) [NullBroadcaster](broadcasters/NullBroadcaster.php) Doing nothing, just a stub
2) [LogBroadcaster](broadcasters/LogBroadcaster.php) Broadcast events to application log
3) [RedisBroadcaster](broadcasters/RedisBroadcaster.php) Broadcast by Redis using Pub/Sub feature

Usage
-----

### Setup Laravel Echo Server

See [docs](docs/laravel-echo-server.md).

### Server side

Add the action to authorize users access to private and presence channels:

```php
class NotificationController extends Controller
{
    // ...
    
    public function behaviors()
    {
        return [
            // ...
            'authenticator' => [
                // define your authenticator behavior
                'class' => HttpBearerAuth::class,
            ]
        ];
    }
    
    public function actions()
    {
        return [
            'auth' => [
                'class' => 'le0m\broadcasting\actions\AuthAction'
            ]
        ];
    }
    
    // ...
}
```

Define a new event to broadcast by extending `le0m\broadcasting\BroadcastEvent`, the public properties you define will be sent as message payload:

```php
namespace common\models;

use le0m\broadcasting\channels\PrivateChannel;
use le0m\broadcasting\BroadcastEvent;

class MessageEvent extends BroadcastEvent
{
    public $text;
    public $author;
    public $time;
    
    private $_postId;


    public function broadcastOn()
    {
        return new PrivateChannel('comments.' . $this->getPostId());
    }

    public function broadcastAs()
    {
        return 'new';
    }
    
    public function getPostId()
    {
        return $this->_postId;
    }
    
    public function setPostId($postId) {
        $this->_postId = $postId;
    }
}
```

And then broadcast it when needed:

```php
$event = new MessageEvent([
    'text' => $text,
    'author' => $user->username,
    'time' => time()
]);
$event->toOthers()->broadcast();
```

The `toOthers` flag is used to broadcast a message to all channel's users _except_ the sender. The socket ID header is used to exclude the sender.

### Client side

Import and initialize `Echo`, then start listening for notifications:

```js
import Echo from 'laravel-echo'
import io from 'socket.io-client'

let postId = 13;
const echo = new Echo({
  broadcaster: 'socket.io', // will default to port 6001 of host
  host: window.location.hostname,
  authEndpoint: '/api/rest/v1/notification/auth', // this can be a whole URL
  client: io, // not needed if `io` is globally defined
  auth: {
    headers: {
      Authorization: `Bearer ...` // set headers needed for the authorization request to private and presence channels
    }
  },
  transports: ['websocket', 'polling'] // give websocket precedence
})

// attach connect event listener, to wait for a socket ID
this.echo.connector.socket.on('connect', () => {
  // console.log(`internal socket id:`, this.echo.connector.socket.id)
  console.log(`socket connected with ID:`, this.echo.connector.socketId())

  // attach listen events
  this.echo
    .private(`comments.${postId}`)
    // the initial dot is to ignore event namespace (derived from backend event class)
    .listen('.new', (event) => {
      console.log(`received comment from Echo:`, event)
    })
})
```

Here we wait for the `connect` event of Socket.io connector, to obtain a socket ID before attaching our callbacks.

Other
-----

- [Echo Server with Docker](docs/laravel-echo-server.md) (bottom)
- [references](docs/references.md)
- [changelog](CHANGELOG.md)
