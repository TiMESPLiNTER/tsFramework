tsFramework
===========

The tsFramework is a very fast and simple to use framework written in PHP. It's built after the MVC pattern and uses the newest philosophy in application development. It's modular and plugins are very easy to develop with some hooks in the request/response process of the framework.

Give it a try. And let me know how you feel about it. ~~(I know it's not well documented at the moment but I'll change this in the near future.)~~ Visit the wiki: https://github.com/TiMESPLiNTER/tsFramework/wiki

To use the framework you can create a minimal `composer.json` file with the following content:

```javascript
{
	"requires": {
		"timesplinter/ts-framework": "dev-master"
	}
}
```

You can also check out the [tsFramework sample site](https://github.com/TiMESPLiNTER/tsFramework-site) repository for a working example.

Core modules
------------
### auth
Provides authentication methods for HTTP auth and authentication with users and rights stored in a database (db module required)

### core
The whole core functions which handles the requests, responses, settings management, etc.

### logger
Gives you the possibility to log your messages to the screen, to a file or into the db. Manage different loggers which can catch different log levels.