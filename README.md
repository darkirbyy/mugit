# Mugit

![version](https://img.shields.io/endpoint?url=https://gist.githubusercontent.com/darkirbyy/07bb4b086f8e7dea73754e73bc5c1bb2/raw/mugit-version.json)
![coverage](https://img.shields.io/endpoint?url=https://gist.githubusercontent.com/darkirbyy/07bb4b086f8e7dea73754e73bc5c1bb2/raw/mugit-coverage.json)

Very lightweight docker image and UI to store and share git repo on a server

## Prerequisite

- Back-end:
  - **Symfony**: 7.4 framework
  - **PHP**: 8.4 (compatible with Symfony 7.4)
  - **Composer**: >= 2.8 for dependency management
- Front-end:
  - **Node.js**: >= 22.x
  - **npm**: >= 10.x for dependency management
  - **Webpack Encore**: 7.x
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
- **stylelint**: for CSS files in `assets/styles` directory
- **eslint**: for JS files in `assets/controllers` directory

To lint all files from one type, run `composer lint-[php|twig|css|js]`.  
To lint all files, run `composer lint-all`.

## Install

After this first install or cloning the existing project:

- install the dependencies with `composer install` and `npm install`.
- copy the `.env` file into a `.env.local` file and customize the values.  
- start the php/web server along with npm server with `symfony server:start -d`.

To use default git hooks, run `git config core.hooksPath ./githooks`. Current hooks are

- prettify and linting all staged files before commit (see [Code quality](#code-quality))
- running tests before push : all tests for `main` branch, unit tests otherwise

## Dev

To increment the version, use `symfony console bizkit:versioning:increment`.

## Test

To start a specific test suite, run `composer tests-[unit|inte|func]`.  
To start all tests, run `composer tests-all`.

## Deploy

A workflow to test, build and deploy the application is preconfigured.  
The workflow can be triggered manually in GitHub Actions or automatically when pushing to main (for prod).
