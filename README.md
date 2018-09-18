# Contao api bundle

![](https://img.shields.io/packagist/v/heimrichhannot/contao-privacy-api-bundle.svg)
![](https://img.shields.io/packagist/dt/heimrichhannot/contao-privacy-api-bundle.svg)
[![](https://img.shields.io/travis/heimrichhannot/contao-privacy-api-bundle/master.svg)](https://travis-ci.org/heimrichhannot/contao-privacy-api-bundle/)
[![](https://img.shields.io/coveralls/heimrichhannot/contao-privacy-api-bundle/master.svg)](https://coveralls.io/github/heimrichhannot/contao-privacy-api-bundle)

A generic API with restricted access to provide access to 3rd party applications.

## Login `/api/login/member` or `api/login/user`

Login is done via symfony `guard` authenticator in combination with contao members `tl_member` or users `tl_user`.
After successful login a volatile token (default: `24 hours`) will be returned that is used for any api and must be provided within request headers `Authorization: Bearer {{token}}`;

```
# test login (with contao front end member)
curl --user username:password -H "Content-Type: application/json" -X POST http://domain.tld/api/login/member

# example response on success
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmXtZSI6ImRpZ2l0YWxlc0BoZWltcmljaA1oYW5ub3QuZGUiLCJpYXQiOjE1MzY4NTYwMDMsImV4cCI6MTUzNjk0MjQwM30.trp-1NgYgXGfHYdE3dlQ8awE8aXUWL-RfBQyfWm2Hz0"
}

# test login (with contao back end member)
curl --user username:password -H "Content-Type: application/json" -X POST http://domain.tld/api/login/user

# example response on success
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmXtZSI6ImRpZ2l0YWxlc0BoZWltcmljaA1oYW5ub3QuZGUiLCJpYXQiOjE1MzY4NTYwMDMsImV4cCI6MTUzNjk0MjQwM30.trp-1NgYgXGfHYdE3dlQ8awE8aXUWL-RfBQyfWm2Hz0"
}
```

## Create an app with an custom api key 

Visit your contao backend at `http://domain.tld/contao?do=api_apps` and create your first app.
Access can be restricted for member or user groups. Admin users `tl_user` will have access to every api by default.
For each request beside the login routes you must provide the generated API `key` as `GET` Parameter.

## Resource /api/resource/{resource_alias}

To add your custom resource, simply add an service within your bundles or app `services.yml`:

```
services:
	  my.api.resource.my_resource:
        class: MyApi\Resource\MyResource
        tags:
        - { name: huh.api.resource, alias: my_resource}
```

Now you are able to access your resource through `/api/resource/my_resource`.

```
# test access to my_resource (provide your token from user or member login and your api key)
curl --header "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmXtZSI6ImRpZ2l0YWxlc0BoZWltcmljaA1oYW5ub3QuZGUiLCJpYXQiOjE1MzY4NTYwMDMsImV4cCI6MTUzNjk0MjQwM30.trp-1NgYgXGfHYdE3dlQ8awE8aXUWL-RfBQyfWm2Hz0" -H "Content-Type: application/json" -X GET http://domain.tld/api/resource/my_resource?key=<api-key>
```

Now you are able to access crud functionality by using the related `HTTP method`:

|   | HTTP-Method | Resource-Method (Mapping) |
|---|---|---|
| /api/resource/my_resource | POST | create() new resource |
| /api/resource/my_resource/23  | PUT | update() existing resource with id 23 |
| /api/resource/my_resource  | GET | list() all resources  |
| /api/resource/my_resource/23  | GET | show() existing resource with id 23 |
| /api/resource/my_resource/23  | DELETE | delete() existing resource with id 23 |

```
# test create() new resource
curl --header "Authorization: Bearer <login-token>" -H "Content-Type: application/json" -X POST -d "{"title":"My test title", "published":true}" http://domain.tld/api/resource/my_resource?key=<api-key>

# test update() existing resource
curl --header "Authorization: Bearer <login-token>" -H "Content-Type: application/json" -X PUT -d "{"title":"My new test title", "published":false}" http://domain.tld/api/resource/my_resource/23?key=<api-key>

# test list() all resources
curl --header "Authorization: Bearer <login-token>" -H "Content-Type: application/json" -X GET http://domain.tld/api/resource/my_resource?key=<api-key>

# test show() existing resource
curl --header "Authorization: Bearer <login-token>" -H "Content-Type: application/json" -X GET http://domain.tld/api/resource/my_resource/23?key=<api-key>

# test delete() existing resource
curl --header "Authorization: Bearer <login-token>" -H "Content-Type: application/json" -X DELETE http://domain.tld/api/resource/my_resource/23?key=<api-key>
```
