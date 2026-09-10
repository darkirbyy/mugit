# μGit

![version](https://img.shields.io/endpoint?url=https://gist.githubusercontent.com/darkirbyy/07bb4b086f8e7dea73754e73bc5c1bb2/raw/mugit-version.json)
![coverage](https://img.shields.io/endpoint?url=https://gist.githubusercontent.com/darkirbyy/07bb4b086f8e7dea73754e73bc5c1bb2/raw/mugit-coverage.json)

Very lightweight docker image and Symfony UI to store and share git repo on a server.

I tried to split as much as possible the core from the UI, so that one can be used without the other, or installed on different machines, but i never tested such cases.

## Core

In this section, all the paths and commands are relatives to the `core` sub-directory.

### Core - Prerequisite

- **docker** and **docker compose** to build and run the docker image.

### Core - Install

- Copy the `.env-example` file into `.env` and customize the values if needed (the `compose.yml` file defines default values for all these variables).
- Prepare the `data` and `keys` folder as specified in the `.env` file (default `./data/dev` and `./keys/dev`).
- Execute the `init-keys.sh` script, the only and first argument must be the folder for storing the keys, as specified before (default `./keys/dev`).

The two last steps are automatically made when starting the UI web server (see [UI - Install](#ui---install)).

## UI

In this section, all the paths and commands are relatives to the `ui` sub-directory.

### UI - Prerequisite

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

### UI - Code quality

**Prettier** with custom modules from `@zackad/prettier-plugin-twig`, `@prettier/plugin-php` and `prettier-plugin-tailwindcss` for php, twig, js and css files.
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

### UI - Install

After this first install or cloning the existing project:

- install the dependencies with `composer install` and `npm install`.
- copy the `.env` file into a `.env.local` file and customize the values.  
- start the php web server with `symfony server:start -d`. Symfony workers are attached to this command so that it also starts the webpack dev-server and a dev instance of the core. The `.env` file for the core must already have been prepared (see [Core - Install](#core---install)).

To use default git hooks, run `git config core.hooksPath ./ui/githooks`. Current hooks are

- prettify and linting all staged files before commit (see [UI - Code quality](#ui---code-quality))
- (disabled) ~~running tests before push : all tests for `main` branch, unit tests otherwise~~

During dev, to increment the version, use `symfony console bizkit:versioning:increment`.

## Test

There are 4 test suites : unit, integration, functionnal and end-to-end.

To start a specific test suite, run `composer tests-[unit|inte|func|e2e]`.  
To start all tests, run `composer tests-all`.

More details about each test suite:

- **unit**: only test main services of the UI, mocking any dependencies.
- **integration** : test the core API. The tests are located in the UI sub-directory to take advantage of all the tools from phpunit framework, but use the minimum code from the UI (only the low-level `CoreExecInterface`, see [SPECIFICATION.md](SPECIFICATION.md)).
- **functionnal**: test that all the routes defined in the UI are reachables and respond correctly. Since the app rely heavily on javascript and turbo drive, these tests cannot simulate real use cases.
- **end-to-end**: test real scenarios with a proper headless web-browser through **Panther**. Require `chromedriver` or `geckodriver` to be installed and availables is the **PATH**. In case of failure, screenshots are saved in `ui/var/panther/screenshots`.

All test suites can work along side dev server. To achieve that:

- during **integration**, **functionnal** and **end-to-end** tests, a second instance of the core is started, named `mugit_test`. The variables from `.env.test` (and `.env.test.local` if needed, especially for the UID and GID) are used as override from the `.env` ones of the core.
- during **functionnal** and **end-to-end** tests, assets are compiled in a second public directory located in `ui/var/panther/public`.

> [!TIP]  
> if **end-to-end** tests test with **Panther** are slow, try to find the `ui/vendor/symfony/panther/src/ProcessManager/WebServerReadinessProbeTrait.php` file and comment out the whole code in `checkPortAvailable` method.

## Deploy

A workflow to test, build and deploy the application is preconfigured. The deploy job covers only the UI.  
The workflow can be triggered manually in GitHub Actions or automatically when pushing to main (for prod).

> [!TIP]  
> To deploy the core,
>
> - either download the artifact created during the workflow, and remove the `compose.override.yaml` used only in dev/test envrionments,
> - or use git clone with a sparse checkout. Example:
>
>   ```sh
>    git clone --no-checkout git@github.com:darkirbyy/mugit.git
>    cd mugit
>    git sparse-checkout init
>    git sparse-checkout set core
>    git co vX.X.X
>   ```
