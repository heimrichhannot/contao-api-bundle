# Changelog
All notable changes to this project will be documented in this file.

## [2.2.1] - 2022-05-04
- Fixed: uitls bundle dependency

## [2.2.0] - 2022-05-02
- Changed: minimum supported contao version is now 4.9
- Changed: make categories bundle optional ([#8])
- Changed: allow php 8
- Changed: allow firebase/php-jwt v5 ([#12])
- Fixed: invalid ctable config
- Fixed: syntax error exception
- Fixed: missing utils bundle dependency
- Fixed: missing symfony/config dependency
- Fixed: dca issue in tl_api_app ([#7])

## [2.1.0] - 2020-10-27

#### Fixed
- symfony 4 compatibility

## [2.0.2] - 2019-07-15

#### Fixed
- services not public (#2) - thx to seibtph

## [2.0.1] - 2019-06-03

#### Fixed
- missing documentation to add custom resource (#4)
- `tl_api_app` toggleIcon missing implementation (#3)
- `tl_api_app_action` options_callback error fixed (#5) 

## [2.0.0] - 2018-12-12

#### Changed
- restructuring of resource handling (resource vs. entity resource)

#### Added
- tl_api_app_action entity for handling action specific configuration

#### Removed
- urodoz/truncate-html from dependencies

#### Changed
- optimized palette handling

## [1.0.2] - 2018-09-21

#### Added
- unit testing to maintain coverage and change things to be more testable

## [1.0.1] - 2018-09-20

#### Fixed
- removed unused code

## [1.0.0] - 2018-09-20

#### Added
- initial version of rest api with login, token handling and resource support including `tl_member` skeleton resource



[#12]: https://github.com/heimrichhannot/contao-api-bundle/issues/12
[#8]: https://github.com/heimrichhannot/contao-api-bundle/issues/8
[#7]: https://github.com/heimrichhannot/contao-api-bundle/issues/7