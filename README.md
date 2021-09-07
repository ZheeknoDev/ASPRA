# Welcome to ASPRA 
A simple PHP micro-framework for build RESTful API service.

# Feature
- Simply, easy to the learning curve.
- PHP OOP (Object-oriented programming)
- PHP MVC pattern design
- RESTFul API.
- Middleware.
- CRUD actions, database entries easily
- SQL query builder
- Request & response with JSON object.
- Request & response with AJAX.
- Validation the request data
- Pretty error interface

# Getting Started
1. PHP 7.x is required
2. Support on MySQL or MariaDB
3. Install the ASPRA and update require of the Composer
	- [filp/whoops](https://github.com/filp/whoops) for pretty error interface
	- [rakit/validation](https://github.com/rakit/validation) for validating data
	- [symfony/var-dumper](https://symfony.com/doc/current/components/var_dumper.html) for dump() 
4. Config application in **/config/app.php**
5. Run SQL file in **/sql/default.sql**

## Run on PHP build-in server
```sh
php -S localhost:8000 -t public
```
or you can use docker to run

# Routing
 **/routers/api.php**
 You can make GET, POST, PUT, PATCH, DELETE
```sh
// Method GET

$router->get('/', function() {
	return "welcome to aspra";
}); 
```
```sh
// Method POST with parameter

$router->post('/example/{username}', function($username) {
	return "Hello there, {$username}";
});
```
With the controller class and method
```sh
// Method POST with the controller class

$router->post('/example/{username}', 'ExampleController:indexMethod');
```

> " : " is the separator between the controller class and method

## Request

- **body()** : get the request body.
- **getPathInfo()** : get the full path of the request.
- **params()** : get list of the request parameters as object.
- **isAjax()** : validate the request is from AJAX.
- **isJson()** : validate the request is from JSON object.
- **isXml()** : validate the request is from xml.
- **isSecure()** : validate the HTTP protocol.
- **requestAPI()** : validate the request is from API.

**with Routing file** 
use *$router->request*
```sh
// Method POST with parameter

$router->post('/example/{username}', function() use ($router) {
	$username = $router->request->params()->username;
	return "Hello there, {$username}";
});
```
**with Controller class**
/router/api.php
```sh
// Method POST with the controller class

$router->post('/example/{username}', 'ExampleController:indexMethod');
```
in ExampleController.php
- you can get the request parameter from the agruments of method, ( on example A )
- or you can get from *$this->request* ( on example B )
```sh
use App\Core\Controller;

class ExampleController extends Controller
{
	public function indexMethod(string $username) // example A
	{
		$username = $this->request->params()->username; // example B
		return "Hello, {$username}';
	}
}
```

## Response
- **json()** : return array values to the JSON object.
- **redirect()** : redirect to path
	- with routing file : *$router->response->redirect();*
	- with controller class : *$this->response->redirect();*
	- or *Response::instance()->redirect();*

## Return the response to JSON Object

**with Routing file**
	- use *$router->response->json( [array] )*, it will return array to JSON object.
```sh
// Method GET

$router->get('/example/json', function() use ($router) {
	return $router->response->json([
		'StatusCode' => http_response_code(),
		'Message' => 'Welcome to my API',
		'Response' => true
	]);
}); 
```
**with Controller class**
in ExampleController.php
- you can return the JSON from *$this->response->json()*
```sh
use App\Core\Controller;

class ExampleController extends Controller
{
	public function indexMethod()
	{
		return $this->response->json([
			'Message' => 'Welcome to my API',
			'Response' => true
		]);
	}
}
```

# Controllers

**Create the controller class in directory /app/controllers/**

/app/controllers/ExampleController.php
```sh
use App\Core\Controller;

class ExampleController extends Controller
{
	public function indexMethod(string $username)
	{
		return "Hello, {$username}';
	}
}
```
## Validation
indexMethod() in the ExampleController, use *$this->validator->validate()*
```sh
public function indexMethod()
{
	$parameter = (array) $this->request->params();
	$validation = $this->validator->validate($parameter, [
		'username' => 'required|alpha_num',
		'password' => 'required',
	]);
}
```
> get more about the available rules with [rakit/validation](https://github.com/rakit/validation#available-rules)


## CRUD with SQL query builder
- use *DB::table()* to stating call the table name in database.
- use *run()* to end the step.

**Create**
```sh
DB::table('example_table')->insert([
	'column_name1' => 'column_value2',
	'column_name2' => 'column_value2',
])->run();
```
**Read**
```sh
DB::table('example_table')
	->where('some_column', '=', 'some_value')
	->or_where('some_column_else', '=', 'some_value_else')
	->run();
```
if you need to select one, use first() instead of run() on the end.

**Update**
```sh
DB::table('example_table')
	->where('some_column', '!=', 'some_column')
	->update([
		'column_name1' => 'column_value2',
		'column_name2' => 'column_value2',
	])->run();
```
**Delete**
```sh
DB::table('example_table')
	->where('some_column', '!=', 'some_column')
	->delete()
	->run();
```

> get more about the query builder component from [PhpToolCase](http://phptoolcase.com/guides/ptc-qb-guide.html)

#  Middlewares

 **Create the middleware class in directory /app/middleware/**
 - /app/controllers/middleware/ExampleMiddleware.php
```sh
use App\Core\Middlewares\InterfaceMiddleware;
use App\Core\Router\Response; 

class ExampleMiddleware implements InterfaceMiddleware
{
	public function handle($request, callable $next)
	{
		// If the request data is from the API
		if($request->requestApi()) {
			// through to the next step
			return $next($request);
		}
		// If not, will return the unauthorized value
		return Respons::instance()->redirect('/401');
	}
}
```
**Register your middleware in   /public/index.php**
```sh
$app->middleware->register([
	'example' => \App\Middleware\ExampleMiddleware::class
]);
```
**with single route**
```sh
// Method GET

$router->get('/example/after/middleware', function() {
	return "You have through the middleware";
})->middleware('example'); 
// name of the middleware that you have registered
```

**with routing group**
- *prefix* : the name of routing group
- *middleware* : list of middleware's name
```sh
// Method GET

$router->group(['prefix' => 'example-group','middleware' => ['example']], 
	function() use ($router) {
		$router->get('/section1, function() {
			return "You have got the section1";
		});
		.....
		
		// with the controller class
		$router->get('/section10, 'ExampleController:indexMethod');
	}
);
```
# License
(MIT License)
Copyright (c) 2020  ZheeknoDev  (million8.me@gmail.com)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions: The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.