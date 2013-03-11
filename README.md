tsFramework
===========

The tsFramework is a very fast and simple to use framework written in PHP. It's built after the MVC pattern and uses the newest philosophy in application development. It's modular and plugins are very easy to develop with some hooks in the request/response process of the framework.

Give it a try. And let me know how you feel about it. (I know it's not well documented at the moment but I'll change this in the near future.)

Changelog
---------
### 0.1.1-alpha 
Cleaned up and restructered a little bit

### 0.1.0-alpha 
Release of the first version. The framework core is still in development and gets improvements but the "how the framework works" at the moment doesn't change anymore. But propably some features will be added in future releases.

Core modules
------------
### auth
Provides authentication methods for HTTP auth and authentication with users and rights stored in a database (db module required)

### autloader
Loads classes out of the filesystem. It also supports phar-packages.

### db
Extends the well known PDO class with some features and breaks it down for a more easy to use experience. (Framework works great without that module but more or less every page uses a db today, so it's included for the moment in the core modules.)

### common
Gives you a swiss pocket knife to work with data types (like strings, arrays, etc.) and relieves you in other work to do with the framework. 

### core
The whole core functions which handles the requests, responses, settings management, etc.

### htmlparser
Needed for the template module it parses html and builds an easy to access and manipulate node tree.

### logger
Gives you the possibility to log your messages to the screen, to a file or into the db. Manage different loggers which can catch different log levels.

### template
A very powerful template engine which can be modular extended with customtags. Developing your own custom tag is very easy and you can do it with minimal PHP skills.
