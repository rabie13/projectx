# projectx
symfony web app for projects management 

Symfony 6.2
php 8.1

* create the admin account (2 methode)
- run the fixture to create a default admin  [ email : admin@projectx.com / password :admin ] and some projects
with the commande php bin/console doctrine:fixtures:load -n
- or you can run the commande php bin/console app:create:user your_email your_password
or just run php bin/console app:create:user to create a default admin [ email : admin@projectx.com / password :admin ]


* login url :/login
* projects management
* user management

* there are some improvements that can be done but unfortunately i run out of time :)
* please contact me if there is a problem