# Willow Framework
Willow is an "opinionated" PHP framework used to quickly create CRUD based RESTful APIs.

Willow is a marriage between [Slim 4](http://slimframework.com) and the [Illuminate Database](https://github.com/illuminate/database).

Opinionated meaning that Willow stresses convention over configuration.

### Requirements
* PHP 7.1+
* MySQL 5.6+
* [Composer](https://getcomposer.org) (Must be installed globably)

### 💾 Installation
Willow is unlike most PHP frameworks. 
It's not a phar and you do not install it directly via Composer.
Nor is it recommended you clone or fork this repo to install.

Instead download the latest tagged package, unpack it, and from the terminal run `composer install` in the base directory.

Once composer finishes it will ask permission to add an alias to your `~/.bashrc` file
(or `~/.zshrc` file if you have replaced the bash shell with zsh). Windows users will need to manually type out the aliased command.
The command added to the `.bashrc` file is: `alias willow='./vendor/bin/robo'`

Willow uses the [robo task runner](http://robo.li/) and the alias allows you to use the
command `willow` instead of having to type out `./vendor/bin/robo` every time you issue a robo command.

Now run the command `willow init`
You You will be prompted with several questions to set up your project. 
You will need to provide **admin credentials** to a running database (supported databases are MySQL, Postgres, SQL Server, and SQLite).
Willow assumes you already have existing entities (tables and views) that will be incorporated in your project.

    