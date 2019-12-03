# slim4-api-skeleton
A small, opinionated skeleton for creating restful apis based on the php micro-framework [slim](http://www.slimframework.com/).
It is derived from the fantastic [slimphp/slim-skeleton](https://github.com/slimphp/Slim-Skeleton) application, and puts it into a professional
php environment stack that is runnable with [docker](https://www.docker.com/why-docker) using [docker-compose](https://docs.docker.com/compose/).

## description
The skeleton should be an example of how one could structure and layout a simple restful api using the popular slim framework in a 
development setting. 
* It should be simple to set up and run. 
* It is highly inspired by one of the main principles of hexagonal architecture:
[seperation of application, domain and infrastructure](https://blog.octo.com/hexagonal-architecture-three-principles-and-an-implementation-example/#principles)
* It is highly testable by allowing the three parts being able to be tested separately.

The api will contain a public part and a protected part. The protected part requires prior login and usage of a JWT Authentication token.

The public part will contain endpoints to:
* submit a simple subscription to different topics containing name and email address, when the user has subscribed on the page, she will 
receive a simple email confirmation hereof, managing the subscriptions will require login however, so a user is created in the process,
and it will be the same as registering a new user
* register a new user, upon registration the user will receive an email with a link to confirm her account
* confirm registration
* request a new confirmation link sent by email, in case the previous has expired and thereby invalid
* login, it will return a JWT token for accessing private endpoints
* request password reset, the user will receive an email with a link to confirm her identity
* validate password reset token provided by the link sent by email
* password reset

The private part will contain endpoints to:
* view a list of subscriptions and which ones the user is subscribed to
* log out

All communication will be done in JSON format.

## technology stack
The stack will be run using docker-compose
* nginx webserver
* php-fpm
* postgres database for persistence
* composer
* mailhog to emulate an email client

## prerequisites
The skeleton was developed using docker engine 19.03.5, one should use a compatible version to run it.

## installation
Clone the repo and bring up the docker instances by issueing the following commands:

    git clone git@github.com:johi/slim4-api-skeleton.git
    cd slim4-api-skeleton
    docker network create demo
    docker-compose run composer install

Before bringing up the goodness, please create a .env file with the following contents:

    DOCKER=1
    DB_DRIVER=pgsql
    DB_HOST=postgres
    DB_PORT=5432
    DB_DATABASE=demo
    DB_USERNAME=develop
    DB_PASSWORD=develop123
    EMAIL_HOST=mailhog
    EMAIL_PORT=1025
    JWT_SECRET=akEECS30AX1qOlYUXh3hPEEKkC5gslaq9ywgEIfXhQbx2/UGXwTVpKuTuZMDDoIuQtncBnyA2Nn2jVvQ/cAJWQ==
    JWT_ALG=HS512
    JWT_EXPIRATION=86400
    SERVER_NAME=api.demo
    PASSWORD_TOKEN_EXPIRATION=86400
    ACTIVATION_TOKEN_EXPIRATION=604800

This is just to give you a quick bootstrap of environment variables before bringing up the docker environment:

    docker-compose up -d
    docker-compose exec php bash
    #the last command will open a terminal session to the php container, from here do:
    php vendor/bin/phinx migrate

## testing
Testing is performed on application (api) level, domain level (domain objects) and infrastructure level, the latter only
as integration test against the database of choice (postgres). The integration test depends on a test database instance 
where tests can be run. The name of the main database being "demo" the test database should be created as "demo_test",
so db name + "_test" suffix. It can be achieved by doing the following:

    docker-compose exec postgres bash
    #this will log you in to the postgres docker container, from here do
    psql demo -U develop
    #this will open the postgres shell, from here do
    create database demo_test;
    #to quit the postgres shell, do
    \q
    #ctrl + d should get you back to your host, now run the tests
    docker-compose exec php php vendor/bin/phpunit
    
## running the demo
The api should now be running under localhost:8080, please open the contained postman collection for example usage.
The Mailhog client should be running at localhost:8025
