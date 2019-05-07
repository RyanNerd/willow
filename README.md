# 🌳 Willow Framework 🌳

>Willow is a type a girl who is beautiful and amazing and is kinda a special person and loved by everyone around her
>most willows can be your best of friends she's up for anything and she's loves anything fun you throw at her...

~ Urban Dictionary

For developers, Willow is an _opinionated_ PHP framework used to quickly create CRUD based RESTful APIs.

Willow is a marriage between [Slim 4](http://slimframework.com) and the [Illuminate Database](https://github.com/illuminate/database);
with [Dependency Injection](http://php-di.org/) as your [best man](https://en.wikipedia.org/wiki/Groomsman), 
[Respect\Validation](https://respect-validation.readthedocs.io/en/1.1/) as your [bridesmaid](https://en.wikipedia.org/wiki/Bridesmaid),
and [Robo](http://robo.li/) as your [wedding planner](https://en.wikipedia.org/wiki/Wedding_planner). 

Willow is _opinionated_ meaning that Willow stresses convention over configuration.

Willow works best as a framework in this situation:
* You need to quickly spin up a [RESTful](https://restfulapi.net/) [datacentric](https://www.codecademy.com/articles/what-is-crud) API
* You have defined your database with entities (tables/views) already in place
* You are just starting your project (for the backend API handler) and need to _hit the ground running_

### 📃 Requirements
* PHP 7.1+
* MySQL 5.6+ (support is planned for Postgres, SQLite, and MSSQL)
* [Composer](https://getcomposer.org) (For Willow to work best this must be installed globally)

### 💾 Installation
From a terminal / command window run:

```
composer create-project ryannerd/willow [your-project-name]
cd [your-project-name]
php -S localhost:8088 -t public
```

Then in your favorite web browser go to: `localhost:8088/v1/sample/hello-world`

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

[See the wiki for more detailed instructions](https://github.com/RyanNerd/willow/wiki/Willow-Framework-Guide)
