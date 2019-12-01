# slim4-api-demo
A small demo of putting together a restful api based on the php micro-framework [slim](http://www.slimframework.com/)

## description
The demo should showcase how one could structure and layout a simple restful api using the popular slim framework in a 
development setting. Meaning it should be simple to set up and run the demo.

The api will contain a public part and a protected part that requires prior login and usage of a JWT Authentication token.

The public part will contain endpoints to:
* submit a simple subscription containing name and email address, when the user has subscribed on the page, she will 
receive a simple email confirmation hereof
* register a new user, upon registration the user will receive an email with a link to confirm her account
* confirm registration
* login, it will return a JWT token for accessing private endpoints
* request password reset, the user will receive an email with a link to confirm her identity
* password reset

The private part will contain endpoints to:
* view a list of subscriptions
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
The demo was developed using docker engine 19.03.5, one should use a compatible version to run the demo.

## installation
Clone the repo and bring up the docker instances by issueing the following commands:

    git clone git@github.com:johi/slim4-api-demo.git
    cd slim4-api-demo
    docker network create demo
    docker-compose run composer install
    docker-compose up -d
    docker-compose exec php bash
    #the last command will open a terminal session to the php container, from here do:
    php vendor/bin/phinx migrate
    
## running the demo
The api should now be running under localhost:8080, please open the contained postman collection for example usage.