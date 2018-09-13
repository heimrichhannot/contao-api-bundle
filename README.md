# Contao api bundle

![](https://img.shields.io/packagist/v/heimrichhannot/contao-privacy-api-bundle.svg)
![](https://img.shields.io/packagist/dt/heimrichhannot/contao-privacy-api-bundle.svg)
[![](https://img.shields.io/travis/heimrichhannot/contao-privacy-api-bundle/master.svg)](https://travis-ci.org/heimrichhannot/contao-privacy-api-bundle/)
[![](https://img.shields.io/coveralls/heimrichhannot/contao-privacy-api-bundle/master.svg)](https://coveralls.io/github/heimrichhannot/contao-privacy-api-bundle)

A generic API with restricted access to provide access to 3rd party applications.

## Login `/api/login`

Login is done via symfony `guard` authenticator in combination with contao members `tl_member`.
After successful login a volatile token (default: `24 hours`) will be returned that is used for any api and must be provided within request headers `Authorization: Bearer {{token}}`;

```
# test login
curl -x POST -u username:password http://domain.tld/api/login

# example response on success
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmXtZSI6ImRpZ2l0YWxlc0BoZWltcmljaA1oYW5ub3QuZGUiLCJpYXQiOjE1MzY4NTYwMDMsImV4cCI6MTUzNjk0MjQwM30.trp-1NgYgXGfHYdE3dlQ8awE8aXUWL-RfBQyfWm2Hz0"
}
```
