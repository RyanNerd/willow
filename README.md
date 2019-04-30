# Willow Framework
## !! Willow currently is in very early alpha (almost unusable) !!
Willow is an "opinionated" PHP framework used to quickly create CRUD based RESTful APIs.

Willow is a marriage between [Slim 4](http://slimframework.com) and the [Illuminate Database](https://github.com/illuminate/database).

Opinionated meaning that Willow stresses convention over configuration.

### Requirements
* PHP 7.1+
* MySQL 5.6+
* [Composer](https://getcomposer.org) (Must be installed globally)

### 💾 Installation

Run:
`composer create-project ryannerd/willow [your-project-name]`
`cd [your-project-name]`

It is recommended you add this `alias willow='./vendor/bin/robo'` to your ~/.bashrc or ~/.zshrc file.
Remember to run `source ~/bashrc` / `source ~/.zshrc` to let your bash shell know you've made chages.
Once you've added the alias then run this command:

`willow init`

If you **did not** create the alias then run this instead:
`./vendor/bin/robo init`

You You will be prompted with several questions to set up your project. 
You will need to provide **admin credentials** to a running database (supported databases are MySQL, Postgres, SQL Server, and SQLite).
Willow assumes you already have existing entities (tables and views) that will be incorporated in your project.

Willow uses the [robo task runner](http://robo.li/) and the alias allows you to use the
command `willow` instead of having to type out `./vendor/bin/robo` every time you run a task.

    