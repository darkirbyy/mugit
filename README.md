# Symfony Template

![version](https://img.shields.io/endpoint?url=https://gist.githubusercontent.com/darkirbyy/07bb4b086f8e7dea73754e73bc5c1bb2/raw/symfo-base-version.json)
![coverage](https://img.shields.io/endpoint?url=https://gist.githubusercontent.com/darkirbyy/07bb4b086f8e7dea73754e73bc5c1bb2/raw/symfo-base-coverage.json)

Template to quick start any Symfony project.

## Initialize (to remove!)

### Local side

After creating the project from the template and cloning it for **the first time**:

- Replace all occurrences of `symfo-base` with the new app name with:

  ```sh
  find . -type f -exec sed -i 's/symfo-base/<new-app-name>/g' {} +
  ```

- Customize the project name and description in the `README` and `composer.json`, and remove this **Initialize** part from `README`.
- Uncomment the desired lines in `.github/workflows/main.yml` to enable the workflow.
- Use git to add, commit and tag this first version with (:warning: don't push yet):

  ```sh
  git add . && git commit -m "Update to version 0.1.0" && git tag -a "v0.1.0" -m "Update to version 0.1.0"
  ```

- Initialize `git flow init` or manually create a `develop` branch.

- (optional) To use bootstrap and some icons, run this command: `npm install --save-dev bootstrap bootstrap-icons @fortawesome/fontawesome-free @popperjs/core`

- (optional) If authentication is needed, configure these files then install `mainick/keycloak-client-bundle` :

  ```properties
  # .env
  ###> mainick/keycloak-client-bundle ###
  IAM_BASE_URL=!ChangeMe!
  IAM_CLIENT_SECRET=!ChangeMe!
  IAM_REDIRECT_URI=!ChangeMe!
  IAM_ENCRYPTION_ALGORITHM=!ChangeMe!
  IAM_ENCRYPTION_KEY=!ChangeMe!
  IAM_VERSION=!ChangeMe!
  ###< mainick/keycloak-client-bundle ###
  ```

  ```yaml
  # config/packages/security.yaml
  providers:
    mainick_keycloak_user_provider:
      id: Mainick\KeycloakClientBundle\Security\User\KeycloakUserProvider

  firewalls:
    ...
    main:
      pattern: ^/
      lazy: true
      provider: mainick_keycloak_user_provider
      entry_point: Mainick\KeycloakClientBundle\Security\EntryPoint\KeycloakAuthenticationEntryPoint
      custom_authenticator:
        - Mainick\KeycloakClientBundle\Security\Authenticator\KeycloakAuthenticator
      logout:
        path: mainick_keycloak_security_auth_logout

  access_control:
    - { path: /auth/keycloak/connect, roles: PUBLIC_ACCESS }
    ...
  ```

  ```yaml
  # config/routes/mainick_keycloak_security.yaml
  mainick_keycloak_security_auth_connect:
    path:       /auth/keycloak/connect
    controller: Mainick\KeycloakClientBundle\Controller\KeycloakController::connect

  mainick_keycloak_security_auth_connect_check:
    path:       /auth/keycloak/check
    controller: Mainick\KeycloakClientBundle\Controller\KeycloakController::connectCheck

  mainick_keycloak_security_auth_logout:
    path:       /auth/keycloak/logout
    controller: Mainick\KeycloakClientBundle\Controller\KeycloakController::logout
  ```

  ```yaml
  # config/routes/mainick_keycloak_client.yaml
  mainick_keycloak_client:
    security:
      default_target_route_name: ...
    keycloak:
      verify_ssl: true
      realm: web
      client_id: 'symfo-base-%app.env%'
      client_secret: '%env(IAM_CLIENT_SECRET)%'
      base_url: '%env(IAM_BASE_URL)%'
      redirect_uri: '%env(IAM_REDIRECT_URI)%'
      encryption_algorithm: '%env(IAM_ENCRYPTION_ALGORITHM)%'
      encryption_key: '%env(IAM_ENCRYPTION_KEY)%'
      encryption_key_path: ''
      version: '%env(IAM_VERSION)%'
  ```

### GitHub side

Some variables and secrets have to been set up:

- Global parameters:
  - **SERV_ADDR** (secret): domaine name for SSH connection
  - **SERV_PORT** (secret): port number for SSH connection
  - **SERV_USER** (secret): user name for SSH connection
  - **SERV_KEYS** (secret): private key for SSH connection
  - **GIST_KEY** (secret): to update the badges information
- Create two environments (stag and prod) and these parameters in each on of them:
  - **MIN_COVERAGE** (variable): valid test job only if coverage if above
  - **SERV_PATH** (secret): where to copy the application on the server

Push the project on `main`/`develop` or both branches to build the application and send it to the server.

### Server side

For each selected environment:

- Create the user and database in MariaDB.
- In the project root, create a `.env.local` file, customizing the `APP_ENV` and `DATABASE_URL` variables. Also check that the `var` directory has been created, otherwise run `mkdir var && chmod 775 var`.
- To correctly route the requests to the application if it lives in a sub-directory, use this nginx location block, replacing `<ENV>` with `prod` or `stag`:

  ```ini
  location /symfo-base {
    return 301 /symfo-base/;
    access_log off; 
  }

  location @symfo-base {
    rewrite ^/symfo-base/(.*)$ /symfo-base/index.php/$1 last;
  }

  location /symfo-base/ {
    alias /usr/share/nginx/<ENV>/symfo-base/public/;
    try_files $uri @symfo-base;

    location ~ ^/symfo-base/index\.php(/|$) {
      fastcgi_split_path_info ^(/symfo-base/index\.php)(/.*)$;
      include fastcgi_params;
      fastcgi_param SCRIPT_FILENAME "${document_root}index.php";
      fastcgi_pass php-fpm:9000;
      internal;
    }
  }
  ```

### Keycloak Side

(optional) If authentication is needed, create a client for each environment.

- for clientId, adapt this structure : `symfo-base-<ENV>`
- :warning: create the required roles without the ROLE_ prefix
- on client scopes, select the dedicated one, then `Add mapper` -> `By configuration`:
  - Client ID: choose the one set before
  - Client Role Prefix: `ROLE_`
  - Token Claim Name: `resource_access.${client_id}.roles`
  - Add to userinfo: ON

## Prerequisite

- Back-end:
  - **Symfony**: 7.4 framework
  - **PHP**: 8.4 (compatible with Symfony 7.4)
  - **Composer**: >= 2.8 for dependency management
  - **MariaDB**: 12.3 through **docker** for the database
- Front-end:
  - **Node.js**: 22.x
  - **npm**: >= 10.x for dependency management
  - **Sass**: >= 1.82
  - **Webpack Encore**: 5.x
- **git** for source and version control
- **symfony CLI** for main commands
- **GitHub** to share and deploy

## Code quality

**Prettier** with custom modules from `@zackad/prettier-plugin-twig` and `@prettier/plugin-php` for twig and PHP files.  
To prettify one file:

- in the console, execute `npm run pretty-file <file>`.
- if using VSCode, install the *Prettier* extension and set the config file path to `linter/.prettierrc.json`, then use *Format Document*.

To prettify all files, run `npm run pretty-all`.

**Linter**:

- **php-cs-fixer**: for PHP files in `src` and `tests` directories
- **twig-cs-fixer**: for twig files in `templates` directory
- **stylelint**: for CSS/SCSS files in `assets/styles` directory
- **eslint**: for JS files in `assets/controllers` directory

To lint all files from one type, run `composer lint-[php|twig|scss|js]`.  
To lint all files, run `composer lint-all`.

## Install

After this first install or cloning the existing project:

- install the dependencies with `composer install` and `npm install`.
- copy the `.env` file into a `.env.local` file and customize the values.  
:information_source: `DATABASE_URL` is not mandatory for dev environment as Symfony will get the correct values from docker.  
- start the php/web server along with docker and npm server with `symfony server:start -d`.
- execute `symfony console doctrine:migrations:migrate`.

To use default git hooks, run `git config core.hooksPath ./githooks`. Current hooks are

- prettify and linting all staged files before commit (see [Code quality](#code-quality))
- running tests before push : all tests for `main` branch, unit tests otherwise

## Dev

To increment the version, use `symfony console bizkit:versioning:increment`.

## Test

To start a specific test suite, run `composer tests-[unit|inte|func]`.  
To start all tests, run `composer tests-all`.

:warning: Tests that require a database connection use a specific database suffixed with `_test`, automatically created when needed. For Symfony to get the `DATABASE_URL` value from docker in test environnement, it's mandatory to run PHPUnit through symfony with `symfony php bin/phpunit`.

## Deploy

A workflow to test, build and deploy the application is preconfigured.  
The workflow can be triggered manually in GitHub Actions or automatically when pushing to main (for prod) or to develop (for stag).
