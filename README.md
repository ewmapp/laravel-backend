# Initial Project Laravel/JWT/GraphQl/Lighthose

## Lighthouse

This is an introductory tutorial for building a GraphQL server with [Lighthouse](https://lighthouse-php.com). While we try to keep it beginner friendly, we recommend familiarizing yourself with [GraphQL](https://graphql.org) and [Laravel](https://laravel.com) first.

## Installing Laravel

---

Create a new project by following installing [Laravel](https://laravel.com/docs/#installing-laravel)

```bash
composer create-project --prefer-dist laravel/laravel=8.6.12 project-name
```

Setup database inside `.env`, consult the [Laravel docs on database configuration](https://laravel.com/docs/database#configuration).

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=database
DB_USERNAME=username
DB_PASSWORD=password
```

Run database migrations to create the users table:

```bash
php artisan migrate
```

Seed the database with some fake users:

```bash
php artisan tinker
# AND
\App\Models\User::factory(10)->create()
```

## Installing Lighthouse

---

Of course, we will use Lighthouse as the GraphQL Server:

```bash
composer require nuwave/lighthouse
```

Publish the default schema to `graphql/schema.graphql`:

```bash
php artisan vendor:publish --tag=lighthouse-schema
```

We will use GraphiQL (opens new window) to interactively run GraphQL queries:

```bash
composer require mll-lab/laravel-graphiql
```

To make sure everything is working, access /graphiql and try this query:

```bash
{
  user(id: 1) {
    id
    name
    email
  }
}
```

## Installing JWT-Auth

---

If you want to easily add secure authentication to Laravel apps, [JSON Web Token Authentication for Laravel & Lumen
By Sean Tymon](https://github.com/tymondesigns/jwt-auth)

Run the following command to pull in the latest version:

```bash
composer require tymon/jwt-auth
```

Add service provider ( Laravel 5.4 or below ) to the providers array in the `config/app.php` config file as follows:

```bash
'providers' => [

    ...

    Tymon\JWTAuth\Providers\LaravelServiceProvider::class,
]
```

Publish the config, run the following command to publish the package config file:

```bash
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
```

Generate secret key, I have included a helper command to generate a key for you:

```bash
php artisan jwt:secret
```

### Setup JWT-Auth

---

Update your User model

```bash
# Add this line
use Tymon\JWTAuth\Contracts\JWTSubject;

# Change this line
class User extends Authenticatable
# To
class User extends Authenticatable implements JWTSubject
```

```bash
# And add
/**
    * Get the identifier that will be stored in the subject claim of the JWT.
    *
    * @return mixed
    */
public function getJWTIdentifier()
{
    return $this->getKey();
}

/**
    * Return a key value array, containing any custom claims to be added to the JWT.
    *
    * @return array
    */
public function getJWTCustomClaims()
{
    return [];
}
```

Configure Auth guard

Note: This will only work if you are using Laravel 5.2 and above.

Inside the `config/auth.php` file you will need to make a few changes to configure Laravel to use the jwt guard to power your application authentication.

Make the following changes to the file:

```bash
'defaults' => [
    'guard' => 'api',
    'passwords' => 'users',
],

...

'guards' => [
    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],
```

Public config file:

```bash
php artisan vendor:publish --tag=lighthouse-config
```

## Initial Auth JWT

---

Setup Middleware routes in `app\Http\Kernel.php`

```bash
protected $routeMiddleware = [
    ...

    // JWT Auth
    'jwt.auth' => \Tymon\JWTAuth\Http\Middleware\Authenticate::class,
];
```

Run the following command to create controller file:

```bash
php artisan make:controller AuthController
```

Sample AuthController file:

```bash
class AuthController extends Controller
{
    public function login()
    {
        return 'login';
    }

    public function logout()
    {
        return 'logout';
    }

    public function refresh()
    {
        return 'refresh';
    }

    public function me()
    {
        return 'me';
    }
}
```

Middler Setup JWT-Auth

Create in `routes\api.php` routes for AuthController:

```bash
Route::post('login', 'App\Http\Controllers\AuthController@login');

Route::prefix('v1')->middleware('jwt.auth')->group(function () {
    Route::post('logout', 'App\Http\Controllers\AuthController@logout');
    Route::post('refresh', 'App\Http\Controllers\AuthController@refresh');
    Route::post('me', 'App\Http\Controllers\AuthController@me');
});
```

Create user for test uses tinker:

```bash
php artisan tinker
# And
$user = new App\Models\User();
# And
$user->name = 'Edmar Cruz'
$user->email = 'edmar@startbsb.com.br'
$user->password = bcrypt('201510')
$user->save();
exit
```

API Playload Model:

```bash
return response()->json([
    'status' => 'success',
    'message' => [
        'description' => 'Login success',
        'data' => [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]
    ]
], 200);
```

```bash
return response()->json([
    'status' => 'error',
    'message' => [
        'description' => '403 Forbidden',
        'data' => []
        ]
    ], 403);
```

## Initial Auth JWT and GraphQl

---

Add @guard(with: ["api"]) in type Query

```bash
type Query @guard(with: ["api"]) {
    "Find a single user by an identifying attribute."
    user(
        "Search by primary key."
        id: ID @eq @rules(apply: ["prohibits:email", "required_without:email"])

        "Search by email address."
        email: String
            @eq
            @rules(apply: ["prohibits:id", "required_without:id", "email"])
    ): User @find

    "List multiple users."
    users(
        "Filters by name. Accepts SQL LIKE wildcards `%` and `_`."
        name: String @where(operator: "like")
    ): [User!]! @paginate(defaultCount: 20)
}
```

Git

```bash
gh repo create
```
