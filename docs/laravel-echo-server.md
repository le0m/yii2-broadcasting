Setup Laravel Echo server
=========================

This guide assumes that your web application and the Echo server are on the same machine, under the same (sub)domain.
The Echo server will handle Socket.io server, while Redis is used as the communication channel between your backend and the Echo server.

See [Echo server docs](https://github.com/tlaverdure/laravel-echo-server) for more details.

Installation
------------

Install the npm package globally:

```shell
$   npm install -g laravel-echo-server
```

Configuration
-------------

Initialize the config file:

```shell
$   laravel-echo-server init
```

The cli tool will help you setup a **laravel-echo-server.json** file in the current directory. You can edit this file to suite your needs:

```json
{
	"authHost": "https://your.domain",
	"authEndpoint": "/endpoint/to/auth/action",
	"clients": [],
	"database": "redis",
	"databaseConfig": {
		"redis": {
			"host": "localhost",
			"port": "6379"
		}
	},
	"devMode": true, // set to false after testing
	"host": null,
	"port": "6001",
	"protocol": "http",
	"socketio": {},
	"sslCertPath": "",
	"sslKeyPath": "",
	"sslCertChainPath": "",
	"sslPassphrase": "",
	"subscribers": {
		"http": false, // disable HTTP API, we will use Redis
		"redis": true
	},
	"apiOriginAllow": { // example CORS configuration, if you enable the HTTP API
		"allowCors": false,
		"allowOrigin": "https://your.domain",
		"allowMethods": "GET, POST",
		"allowHeaders": "Origin, Content-Type, X-Auth-Token, X-Requested-With, Accept, Authorization, X-CSRF-TOKEN, X-Socket-Id"
	}
}

```

Then start the server:

```shell
$ laravel-echo-server start
```

To stop it:

```shell
$ laravel-echo-server stop
```

Enable HTTPS
------------

I find that setting an nginx proxy is the simplest solution to enable HTTPS communication between the client and the websocket.

This is a **sample nginx proxy config**:

```
location /socket.io {
	proxy_pass http://localhost:6001;
	proxy_http_version 1.1;
	proxy_set_header Upgrade $http_upgrade;
	proxy_set_header Connection "Upgrade";
}
```

The `/socket.io` path is the default for the Echo instance, this will proxy requests sent to your HTTPS-enabled domain to the local Echo server instance (running on port 6001 by default).

**A different nginx config** i came across is this:

```
upstream websocket {
    server localhost:6001
}

// ...

server {
    // ...

    location /socket.io {
    	proxy_pass https://websocket;
    	proxy_http_version 1.1;
    	proxy_set_header Upgrade $http_upgrade;
    	proxy_set_header Connection "Upgrade";
    }
}
```

You may have to fiddle with `localhost`/`your.domain` in the upstream definition, but this should be a better solution.

Using Docker
------------

In `docker` folder you can find example files for creating a Docker image.
You can use both a default `laravel-echo-server.json` configuration file, or override some parameters with variables defined in `.env` file:

1. rename `.env-example` to `.env` and adjust variables according to your needs (refer to [Echo Server docs](https://github.com/tlaverdure/laravel-echo-server#dotenv)); you can use this to separate dev/prod environments
1. adjust shared configuration in `laravel-echo-server.json`; this file will be added and used by the built Docker image
1. from `docker` folder, run:
   ```shell
    $ docker build -t echo-server .
    $ docker exec -it --rm --name echo -p 6001:6001 echo-server
   ```
