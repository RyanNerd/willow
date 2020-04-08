# 🌳 Willow Framework 🌳

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

>[![willow](https://raw.githubusercontent.com/RyanNerd/willow/master/willow.png)](https://willow.plexie.com/app/#/public/project/f66cdc9e-18dd-419c-8575-0c8901152cd3) Willow is a type a girl who is beautiful and amazing and is kinda a special person and loved by everyone around her
>most willows can be your best of friends she's up for anything and she's loves anything fun you throw at her...

~ [Urban Dictionary](https://www.urbandictionary.com/define.php?term=Willow)

For developers, Willow is an _opinionated_ PHP framework used to quickly create CRUD based RESTful APIs.

💒Willow is a marriage between [Slim 4](http://slimframework.com) and [Eloquent ORM](https://github.com/illuminate/database)
with [Robo](http://robo.li/) as your [wedding planner](https://en.wikipedia.org/wiki/Wedding_planner).

For instructions and getting started see the [Willow Framework User Guide](https://www.notion.so/Willow-Framework-Users-Guide-bf56317580884ccd95ed8d3889f83c72)

Willow works best as a framework in this situation:
* You need to quickly spin up a [RESTful](https://restfulapi.net/) [datacentric](https://www.codecademy.com/articles/what-is-crud) API
* You have defined your database with entities (tables/views) already in place
* You are just starting your project (for the backend API handler) and need to _hit the ground running_

### 📃 Requirements
* PHP 7.4+ (Willow 2.0+)
* PHP 7.2+ (Willow 1.1+)
* MySQL 5.6+ or SQLite3 (Postgres and MSSQL should also work but are untested)
* [Composer](https://getcomposer.org) (For Willow to work best this must be installed globally)

### 💾 Installation
To install Willow version 2.0 run:

```
composer create-project ryannerd/willow:^2 [your-project-name]
cd [your-project-name]
```

To install Willow version 1.1 use these commands:

```
composer create-project ryannerd/willow:^1.1 [your-project-name]
cd [your-project-name]
```

### Sample

```
// Linux / Mac users do this:
./willow sample

// Windows users execute this:
php -S localhost:8088 -t public
// Then in your favorite web browser go to: localhost:8088/v1/sample/hello-world
```

The result should look something like this:

```json
{
  "authenticated": true,
  "success": true,
  "status": 200,
  "data": {
    "id": "hello-world"
  },
  "missing": [ ],
  "message": "Sample test",
  "timestamp": 1556903905
}
```

###  Willow Commands

```bash
./willow docs     # bring up the documentation web page
./willow list     # list all available Willow commands
./willow sample   # launch the sample API in a web browser
./willow generate # create controllers and actions for a given table and route
./willow test     # execute the unit tests
./willow banner   # show the Willow introductory banner

# Database commands
./willow db:init         # initialize the .env file for database access
./willow db:show-tables  # list all the tables in the database
./willow db:show-views   # list all the views in the database
./willow db:show-columns # list all the columns for a given table
```

<small>
Willow icon made by <a href="https://www.freepik.com/" title="Freepik">Freepik</a> 
from <a href="https://www.flaticon.com/" title="Flaticon">www.flaticon.com</a>
is licensed by <a href="http://creativecommons.org/licenses/by/3.0/" title="Creative Commons BY 3.0" target="_blank">CC 3.0 BY</a>
</small>
