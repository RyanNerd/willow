# Willow Framework
## !! Willow currently is in very early alpha (almost unusable) !!

>Willow is a type a girl who is beautiful and amazing and is kinda a special person and loved by everyone around her
>most willows can be your best of friends she's up for anything and she's loves anything fun you throw at her...

-Urban Dictionary

For developers, Willow is an _opinionated_ PHP framework used to quickly create CRUD based RESTful APIs.

Willow is a marriage between [Slim 4](http://slimframework.com) and the [Illuminate Database](https://github.com/illuminate/database);
with [Dependency Injection](http://php-di.org/) as your [best man](https://en.wikipedia.org/wiki/Groomsman), 
[Respect\Validation](https://respect-validation.readthedocs.io/en/1.1/) as your [bridesmaid](https://en.wikipedia.org/wiki/Bridesmaid),
and [Robo](http://robo.li/) as your [wedding planner](https://en.wikipedia.org/wiki/Wedding_planner). 

Willow is _opinionated_ meaning that Willow stresses convention over configuration.

Willow works best as a framework in this situation:
* You need to quickly spin up a [RESTful](https://restfulapi.net/) [datacentric](https://www.codecademy.com/articles/what-is-crud) API
* You have defined your database with entities (tables/views) already in place
* You are just starting your project and need to _hit the ground running_

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
    "id": "helllo-world"
  },
  "missing": [ ],
  "message": "Sample test",
  "timestamp": 1556903905
}
```

To initialize your project for Linux/Mac users it is recommended you add this `alias willow='./vendor/bin/robo'` 
to your ~/.bashrc or ~/.zshrc file.

Remember to run `source ~/bashrc` / `source ~/.zshrc` to let your shell know you've made chages.

For Windows users you need to create a `willow.bat` file (similar to Linux) and add this to your PATH environment.

Once you've added the alias then run this command:

`willow willow:init`

If you **did not** create the alias then run this instead (Linux/Mac):
`./vendor/bin/robo willow:init`

When you run this command you will be prompted for the following:
- **admin credentials** to a running MySQL/MariaDB database.
- Which entities (tables/views) should have a Model, Controller, and Actions generated for them.

See the wiki for more detailed instructions once your project is initialized.

### 📦 Deployment
Once you have your project working in development here are the recommendations for deploying to production.
* Place your project in a managed repository such as Github.
* In the production enviroment pull the repo.
* Make a copy of the `.env` file or use the included `env-example` file filling in the values for your production environment.
* Follow the guides for [Slim](http://slimframework.com) for your chosen web server.
* If you configure your web server to handle CORS then you may want to remove CORS handling from `app/Main/app.php`
