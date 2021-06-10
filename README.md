# 🌳 Willow Framework 🌳

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

### TODOs
TODO:
- Code formatting
- Target PHP version prompt and logic.
- Unit tests rewrite?


>[![willow](https://raw.githubusercontent.com/RyanNerd/willow/master/willow.png)](https://willow.plexie.com/app/#/public/project/f66cdc9e-18dd-419c-8575-0c8901152cd3) Willow is a type a girl who is beautiful and amazing and is kinda a special person and loved by everyone around her
>most willows can be your best of friends she's up for anything and she's loves anything fun you throw at her...

~ [Urban Dictionary](https://www.urbandictionary.com/define.php?term=Willow)

For developers, Willow is an _opinionated_ PHP framework used to quickly create CRUD based RESTful APIs.

Willow is a marriage 💒 between [Slim](http://slimframework.com) and [Eloquent ORM](https://github.com/illuminate/database)
with [Robo](http://robo.li/) as your [wedding planner](https://en.wikipedia.org/wiki/Wedding_planner).

For instructions and getting started see the [Willow Framework User Guide](https://www.notion.so/Willow-Framework-Users-Guide-bf56317580884ccd95ed8d3889f83c72)

Willow works best as a framework in this situation:
* You need to quickly spin up a [RESTful](https://restfulapi.net/) [datacentric](https://www.codecademy.com/articles/what-is-crud) API
* You have defined your database with your tables already in place
* You are just starting your project and need to _hit the ground running_

### 📃 Requirements
* PHP 7.4+ (Willow 3.0+)
* Databases:
    - MySQL 5.6+
    - SQLite3
    - Postgres (untested)
    - MSSQL (untested)
* [Composer](https://getcomposer.org) (For Willow to work best this must be installed globally)

|Notes:|
|Willow will work on Windows only if running in the Windows Subsystem for Linux (WSL)|
|Previous versions of Willow are no longer supported (sorry I don't have the bandwidth to support these)|

### 💾 Installation
To install Willow run:

```bash
composer create-project --ignore-platform-reqs ryannerd/willow:^3 [your-project-name]
cd [your-project-name]
```

This will create a skeleton Willow project. Willow tries to symlink to [robo](https://robo.li/) You can then use Willow (robo) commands to build your app.

### Demo

```bash
./willow sample
```

The result in your browser should look something like this:

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

###  Willow (robo) Commands

```bash
# Documentation and demo
./willow docs   # bring up the documentation web page
./willow list   # list all available Willow commands
./willow sample # launch the sample API in a web browser
./willow banner # show the Willow introductory banner

# Willow core commands
./willow make   # Connects to your database and builds routes, controllers, models, actions, etc.
./willow reset  # Resets the project back to factory defaults
./willow eject  # Removes the sample artifacts from the project

# Database commands
./willow tables  # list all the tables in the database
./willow details # Show details (column names and types) of a selected table
```

### Contributing

Do this:
1. Fork this repo
2. Make changes on your fork
3. Push a PR

Note: the main branch isn't `master` it's `3.x` which is where you want to push your PR.


<div align="center">

Special thanks to:

[The Slim Framework](https://slimframework.com)

[Illuminate / Eloquent ORM](https://github.com/illuminate/database)

<small>
Willow icon made by <a href="https://www.freepik.com/" title="Freepik">Freepik</a>
from <a href="https://www.flaticon.com/" title="Flaticon">www.flaticon.com</a>
is licensed by <a href="http://creativecommons.org/licenses/by/3.0/" title="Creative Commons BY 3.0" target="_blank">CC 3.0 BY</a>
</small>
</div>
