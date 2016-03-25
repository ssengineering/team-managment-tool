# Team Management Tool (TMT)
This is the OIT Team Management Tool (TMT) repository. Below is what you will need to know to get started.

## Setting up the Development Environment
The development environment almost exactly mirrors the TMT production environment. Once it is all set up, you will be able to navigate to `localhost` and access your local version of the TMT. All the microservice containers run from images, so if you want to work on those and change them, you will have to do so another way. If you want to change the code in the TMT repository, however, you will be able to modify the code and instantly see the changes.

1. First and foremost, the TMT runs inside Docker containers. Make sure Docker and Docker-Compose are installed.
2. Before anything will work properly, you will need to have access to the TMT docker registry. Contact one of the TMT engineers and get a key that can access the docker registry. Place this key in `/etc/docker/certs.d/<registry_url>/ca.crt`.
3. From the root of the repo run `./initialize.sh`. This script will import and setup the databases for the TMT and all the microservices, install PHP dependencies through Composer, pull all the necessary docker images, and generate all the RSA keys necessary to allow the environment to function properly.
4. Run `docker-compose up -d`.
5. You should now have your development environment up and running!

## Development Environment Architecture
The TMT runs entirely in Docker containers. The entire application is composed of several containers that run our code (TMT container and microservice containers), and several that are supporting images (mysql, redis, etc.). The only container that is accessed directly is the TMT container. The communication to the microservices is done through the TMT container. All outside requests to an authenticated microservice will be rejected because the only communication the microservices accept is from the TMT. This is handled through RSA public/private keys. All the microservices in the development environment currently run from the latest image. The TMT container knows how to commmunicate with the microservices through environment variables that indicate what url it should call out to. If you are working on a microservice, or for whatever reason want to insert a different version in place of the current microservice, you will need to edit `docker-compose.yml` to change the environment variable to the needed url/port for the corresponding microservice in the TMT section.

## TMT Code Structure
The TMT makes use of two custom frameworks, the old one and the new one. The old one is being phased out in favor of the new framework. The two work in parallel for now, until the old framework is entirely replaced.

### Old Framework
- This framework simply serves flat files from the `app/` folder.
- The files for each app are generally organized at the `app/<app name>` directory.
- Has lower preference than the new framework.

### New framework
- Makes use of an object oriented architecture with models, data accessors, controllers, views and apis.
- It takes precedence over the old framework.
- Intelligently routes requests and does not serve flat files

The organization for the new framework is key to making it work properly. The code is organized into the following directories under the `app/` directory:

- accessors/
- apis/
- applications/
- controllers/
- exceptions/
- libs/
- models/
- static/
 - css/
 - html/
 - images/
 - js/
- views/

What is in each of these directories should be pretty intuitive. The details for how to create objects and their exact functionality in each category is documented below:

**Note:** All class names should match their file name!

#### Accessors
**Namespace**: `TMT\accessor`

**Extend**: `MysqlAccessor` **or** `MongoAccessor`

These are classes for accessing the database. **No other code should touch the database!** All of them should extend either the `MysqlAccessor` or `MongoAccessor` base classes. In turn, both of these classes extend from the `Accessor` base class. The database connection variable (an instance of the `PDO` class) can be accessed with `$this->pdo`.

#### Apis
**Namespace**: `TMT\api\<app name>`

**File Structure**: `app/apis/<app name>/<endpoint name>.php`

**Extend**: `\TMT\APIController`

These classes act as API route endpoints. The namespace and class names form the url route that the class responds to. For example, the employee api. The file `app/apis/employee/terminate.php` should be in namespace `TMT\api\employee` with class name `terminate` and corresponds to the url `<domain>/apis/employee/terminate`. If a route with only one level is needed, such as `<domain>/apis/employee`, then the file should be found at `app/apis/employee/index.php` with class name `index`. Inside the class, the function names correspond to the HTTP method of the call. If a GET request is made, it will route to the function get(), POST to post() and so on. (Only GET, POST, PUT, and DELETE are supported)

There is a second type of API that is currently being used in the TMT. Since the TMT now makes use of microservices, there are API gateway classes. These are simply proxy endpoints to the real microservice which make calling the microservices much more flexible and easy to work with. Whichever endpoints a microservice might have, there should be a corresponding endpoint in an API class on the TMT. These classes, in the constructor, should have a little logic to determine which url to direct the requests to. This url should be specified by an environment variable and should probably specify a default. **Note!** Before doing anything else, the constructor should call `parent::__construct();` or strange behavior may occur. Then each of the functions on the API gateway class should simply call `$this->requireAuthentication();`, assuming that authentication is desired, and then call `echo json_encode($this->sendAuthenticatedRequest());` The function to make an authenticated request should be used in the following way:

- The first parameter is the HTTP method, "GET", "POST", etc.
- The second parameter is the url to hit. The url should be formed in the following way: `$this->url."/route"`. If GET data, or query parameters are included, call `$this->url."/route?".http_build_query($params['request'])`. It may also be necessary to tack on parameters from `$params['url']` to form the correct route.
- The third parameter is any POST data to include. This should just be `$params['request']` if the POST data is required.

The `sendAuthenticatedRequest` function simply makes an HTTP request to the specified url with the given data and method. It tacks on an additional header to the request for authentication, and a Json Web Token that has been encoded and signed with an RSA private key. The microservice (assuming that it is written in Go and is using the Eden framwork) will then decode the JWT with the user's information in it, and then try to verify the signature. If the public key the microservice has matches the private key the JWT was signed with, then the request is considered authenticated.

#### Applications
**Namespace**: `TMT\app`

**Extend**: `\TMT\App`

This folder can be thought of as possible pages that a user can navigate to. The name of the class and the function names mirror the url that will be hit. For example the function `table` in the `employeeList` class would be navigated to with the url `<domain>/employeeList/table`. The function index can be used for a url with only one level. For example, a request to `<domain>/employeeList` would route to the `employeeList` class and the `index` function. In each of these functions, you will want to render a page. See the section on views for more information on how to do this. Other tasks include requiring authentication and possibly gathering data needed in order to properly render the page.

#### Controllers
**Namespace**: `TMT\controller`

**Extend**: `\TMT\Controller`

This directory containers miscellaneous classes that don't fit in anywhere else. Typically, the rule for putting a class here is if it does not fit in any of these categories, then it should probably go here, especially if an api or other class creates an instance of the class from this directory. An example of a class in the controllers category would be the EmailHandler. This class as you can imagine, is used to send emails, a task that should be managed by a single class and that shouldn't necessarily be solely the job of the API or page that requires an email to be sent.

#### Exceptions
**Namespace**: `TMT\exception`

The contents of this directory is simple, any custom exception class that is needed for the TMT should be put here.

#### Libs
This folder contains miscellaneous classes that support the framework, including the router for the framework, and the base classes for apps, apis, and controllers. There should typically be little need to change these classes. It should also be noted that the files `app/autoload.php` and `app/init.php` are not found in this folder, but they provide key functionality to the framework. The autoload class routes requests sent to the TMT. This class is set up in `init.php`. These files should only need modification in the case that a new route needs to be added the framework, which is unlikely.

#### Models
**Namespace**: `TMT\model`

**Extend**: `Model`

These files simply contain classes that are used to hold data. Typically they will just be to represent an entry from the database, but they don't necessarily have to.

#### CSS
**File Structure**: `app/static/css/<app name>/<file name>.css`

These are just static css files that need to be included by a page.

#### JS
**File Structure**: `app/static/js/<app name>/<file name>.js`

These are just static js files that need to be included by a page.

#### HTML
**File Structure**: `app/static/html/<app name>/<file name>.html`

These are just static html files that need to be included by a page. Typically, only dialog boxes or other such html "modules" that make sense to break out into a separate file would go here.

#### Images
This folder is simple, just put any necessary image files in this directory.

#### Views
**File Structure**: `app/views/<app name>/<file name>.twig`

The TMT uses twig templates to render front ends. The naming for .twig files in the TMT does not have any special meaning. Each of these views is rendered through the framework by calling, `this->render()` from a class that extends `\TMT\App`. This function takes two arguments. The first is the path to the view file, omitting the .twig extension. These paths are routed to from the `views/` folder. So to render the template at `app/views/employeeList/table.twig` you would call `$this->render("employeeList/table")`. Optionally, you can specify a second parameter. This second parameter should simply be an array of data that is to be passed in to render the template. Certain information that is commonly needed is already given when render is called. Check `app/libs/App.php` in the `render` function to check if the necessary data is provided. If it is not, you can pass any extra data needed to render the template in through the second parameter to `render`. Any .twig file that should have the standard header with the links and everything, should call `[% extends "templates/main.twig" %]` at the beginning of the file. Any .js/.css/.html files that need to be included should be included within the block "headerScripts" or "footerScripts". The main content of the page should be in the block "content".

## Unit and Integration Tests
The tests are run inside containers as well, through PHPUnit. The tests should be written in files that match this pattern: `test/<unit | integration>/<type/app>/<Name of test file>Test.php`, (e.g. `test/unit/accessors/EmployeeTest.php`.

To run the unit tests, run `docker-compose -f unit-test.yml up`.
To run the integration tests, run `docker-compose -f integration-tests.yml up`.

These commands will run all the corresponding tests. Output from the tests is stored in `test-results.txt`. You might want to clear this file or remove it occasionally.

## Connecting a new Microservice
Connecting a new microservice should be fairly straightforward with the set up that currently exists, but it takes a little bit of work. Below are the steps to take to add a new microservice

- Build the microservice
- Create an API gateway as described above in the APIs section to make secure, authenticated calls from the TMT to the microservice
- Ensure that all requests to the microservice from the front end are routed to the gateway on TMT, not to the microservice directly
- Add another section to `docker-compose.yml` for the microservice and set up the `dbimport` image to make any setup modifications needed for the new microservice in the development environment.
- In `docker-compose.yml`, set up the volumes, RSA key files, the `PRIVATE_KEY_FILE` and `KEYS_DIRECTORY` environment variables, the image of the microservice, and make sure that the correct ports are exposed, and the TMT links to the microservice.
- It should now be connected!
