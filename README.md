# Drupal Site Template

Drupal Website

## Website Development

We assume the following development style (using the docker terminology to refer
to your computer as the "host" to distinguish it from the containers):

- Files are edited on your host system using your editor of choice.
- **All** commands (`composer`, `npm`, `drush`, `phpunit` etc.) are run from
  within their respective container via `docker-compose exec`.

This template has a separate (single command) step to install Drupal which you
must run after you have started the containers (see below).

We did this to avoid baking things like DB credentials into the images. If you
decide you do want to bake some of those steps into the image then you can move
commands from `dev/config/services/drupal/root/setup.sh` into
`Dockerfile.drupal.dev`.

The `dev/config/` dir is organised first by service (see `docker-compose.yml`
for the list of services) and then by the path within the container that the
file/folder will be copied/mounted i.e.

```
dev/config/services/{NAME_OF_DOCKER_COMPOSE_SERVICE}/{PATH_THAT_FILE_IS_COPIED_OR_MOUNTED_IN_THE_SERVICE}
```

### Getting started

Please note that each command below is prefixed by a shell prompt which tells
you whether this command should be run on your docker host (`your-computer`
below) or one of the containers.

```shell script
## Terminal 1 ########

## start all the services (add --detach arg to this command if you
## want it to return control of your terminal to you)
you@your-computer$ docker-compose up --build

## Terminal 2 ########

## Deploying or updating our drupal site

## The process above takes care of loading a database dump with our site. Because the database dump does
## not have all recent updates from modules or some may require specific initialisation, you will have
## to run updatedb, update our site configuration from source and clear the cache.

## These steps are bundled into `./bin/post-deploy`.

you@your-computer$ sh bin/post-deploy

## Terminal Work

## To run commands e.g. composer or mysql, we open a shell in the drupal
## container and run them from there
you@your-computer$ docker-compose exec drupal bash

## When you have a shell in the drupal container you can ...
## ... follow along with the nginx logs
drupal-container$ tail -f /var/log/nginx/error.log
drupal-container$ tail -f /var/log/nginx/access.log

## ... connect to mysql
drupal-container$ mysql
```

If you prefer you can run commands directly via docker-compose without having to
open the bash shell first e.g.

```shell script
## Alternatively you can pass the command you want to run directly to
## `docker-compose exec` e.g. run mysql CLI client
you@your-computer$ docker-compose exec drupal mysql
```

After you install Drupal (see above), the following should be available in your
web browser:

- http://localhost:8080/ (Drupal in the `drupal` container)
- http://localhost:3001/ (Browsersync in the `frontend` container)


### Configuration Management
All config management is run via the two drush commands:

```shell script
drupal-container$ drush config-import --yes
```
 or

```shell script
drupal-container$ drush cim
```

and

```shell script
drupal-container$ drush config-export --yes
```

or

```shell script
drush cex
````

These commands are bundled in ./bin/ so you can run them from your host directly.

Any config that you set within your local admin, you should export to files and commit in your feature branches. On doing a pull after anyone has merged in a feature branch, you should do an import.

Note: Currently, development only modules are being deployed to Pantheon. This is because their configuration is exported along with all other config, and when running config-import on Pantheon, it expects those modules to exist. Deploying dev modules is a temporary fix until we can split config per environment.

### Frontend
There is a front end container which will run all necessary watchers for front end development. Nothing needs to be done, these will start up with docker-compose up.

Foundation SCSS and JS are both included in the custom theme, with the majority of components turned off. These can be enabled as needed.

The only front end task that does not happen on watch is compiling of the svg sprite. This can be done manually when a new icon is added with
`docker-compose exec frontend npm run svgsprite`

The svgsprite is included in the html.html.twig template. A sprite icon can be used anywhere else by including the partial:
`{% include directory ~ '/templates/partials/svg-icon.twig' with { icon : 'search', width: 20, height: 20 } %}`


### Adding custom module code

Create custom modules in `modules/custom` and they will automatically be
available to the Drupal container.

If your custom module is in a separate git repo then you can clone into that dir
and that will also be fine.

### Running Tests Manually

```sh
you@your-computer$ docker-compose exec drupal bash

## NB: VERY IMPORTANT: YOU MUST RUN TESTS AS www-data USER
## We have to run PHPUnit as a non-root user (otherwise it seems to fall back to
## running as the 'nobody' user who has no permissions to do anything)
root@drupal-container> su www-data
www-data@drupal-container> cd /var/www/web
www-data@drupal-container> phpunit --verbose modules/my-module-under-development/
```

### Running tests as CI does

This runs all tests the same way CI does.

```sh
you@your-computer$ docker-compose  exec drupal /root/run-ci.sh
```

### Drupal admin credentials

The default credentials are set to:

    Email:    admin@example.com
    Password: admin

### SAML Credentials

The environment provides a SAML IdP and configured drupal to use Single Sign On over this service.
The SAML IdP contains 4 users that can be used. Two of them have already been created in Drupal
while the other two have not.

| account name | password  | email                 | has account |
| ---          | ---       | ---                   | ---         |
| samluser1    | samluser1 | samluser1@example.com | yes         |
| samluser2    | samluser2 | samluser2@example.com | yes         |
| samluser3    | samluser3 | samluser3@example.com | no          |
| samluser4    | samluser4 | samluser4@example.com | no          |

If you need to configure more users please refer to this file:
`./dev/config/services/saml-idp/var/www/simplesamlphp/config/authsources.php`

### Documentation

- Drupal API Reference https://api.drupal.org/api/drupal
- Drupal coding standards
  https://www.drupal.org/docs/develop/standards/coding-standards
- Drupal Core Changelog https://www.drupal.org/list-changes/drupal

### Caveats

- The Drupal site is in cache disabled mode for to make templating/frontend
  easier.
- **Accessing the site as an anonymous user still makes use of caching even when
  local development settings have been enabled. You must be logged in to view
  your site with caches disabled.**

### Cleaning up

```
## clean up (-v also deletes the volume that stores the MySQL data so only use
## it if that's the result you want)
you@your-computer$ docker-compose down -v
```

### Deployments

Github Actions are in use in order to deploy updates directly to the Pantheon site, with the following rules:

- Any push to the `main` branch will get automatically deployed to the Pantheon Dev environment.
    This includes an `npm build` and `composer install`. This is done by pushing the main branch
    to the master branch on the Pantheon repo.
- In order to import config or run other drush commands on the Pantheon site, you will need to use Terminus.
- Any push of a branch prefixed wtih `ft-` will also create or push to a branch of the same name
    on the Pantheon repo. You can then login to the Pantheon dashboard and use this branch to
    create a multidev environment if necessary. Ideally this would be done automatically, but
    currently Terminus, the Pantheon CLI, is not installing correctly via Github Actions.
- Multidev branches cannot have more than 11 characters, or include a slash. The character count
    includes the `ft-` prefix.
- There is currently no automated removal of branches. If a feature branch is tested and merged into main,
    the autodeploy to the Dev environment will happen, but when you delete the feature branch, it will still
    exist on the Pantheon repo. The Pantheon interface gives connection details for it's repo, you can clone
    that and manually remove the branch until this is automated.


