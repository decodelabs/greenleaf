## v0.8.2 (2025-05-15)
* Added automated Action discovery

## v0.8.1 (2025-05-09)
* Moved dev cache refresh to Middleware
* Improved not found and exception handling

## v0.8.0 (2025-05-09)
* Upgraded Harvest to v0.5.0
* Moved Middleware Attribute to root
* Implemented PageAction Middleware
* Removed Middleware const list

## v0.7.2 (2025-05-02)
* Upgraded Guidance to v0.2.0

## v0.7.1 (2025-04-15)
* Fixed named parameter key types
* Bumped dev version

## v0.7.0 (2025-04-15)
* Built PatternSwitch Router
* Added Collector Generator with caching
* Added Caching interfaces for Router and Generator
* Moved Compiler ns to Route
* Renamed createUrl() to url()
* Added named properties to url()
* Renamed Matching Router to CheckEach
* Load Routers and Generators with Slingshot
* Load Generators on demand

## v0.6.2 (2025-04-11)
* Added Page routes
* Added PageAction interface
* Added HTML PageAction
* Made Action dispatch available in ActionTrait

## v0.6.1 (2025-04-09)
* Upgraded Slingshot dependency

## v0.6.0 (2025-03-14)
* Integrated Harvest Transformer
* Allow mixed response from Actions
* Wrapped execute args in LeafRequest

## v0.5.0 (2025-03-05)
* Removed areas
* Removed Action class suffix

## v0.4.1 (2025-02-20)
* Upgraded Coercion dependency

## v0.4.0 (2025-02-16)
* Replaced accessors with property hooks
* Added @phpstan-require-implements constraints
* Upgraded PHPStan to v2
* Tidied boolean logic
* Fixed Exceptional syntax
* Added PHP8.4 to CI workflow
* Made PHP8.4 minimum version

## v0.3.0 (2024-08-21)
* Converted consts to PascalCase
* Updated Veneer dependency and Stub
* Removed unneeded LazyLoad binding attribute

## v0.2.1 (2024-07-17)
* Updated Veneer dependency

## v0.2.0 (2024-04-29)
* Simplified Archetype resolution

## v0.1.10 (2024-04-29)
* Fixed Veneer stubs in gitattributes

## v0.1.9 (2024-04-26)
* Updated Archetype dependency
* Updated dependency list

## v0.1.8 (2024-04-05)
* Support Attributes for defining Action Middleware

## v0.1.7 (2024-01-23)
* Fixed CORS with methodBy<parameter> actions

## v0.1.6 (2024-01-23)
* Added methodBy<parameter> Action signatures

## v0.1.5 (2023-12-13)
* Added number validator

## v0.1.4 (2023-12-12)
* Added JsonApi action type
* Improved OPTIONS request handling
* Added matching route to Request attributes

## v0.1.3 (2023-12-11)
* Improved JSON error handling

## v0.1.2 (2023-12-08)
* Added Exception handling to Actions
* Added Uuid Validator
* Added OPTIONS request check in matcher

## v0.1.1 (2023-11-27)
* Added Middleware support to Actions
* Use request attributes in Action Slingshot
* Switched to Slingshot for invocation
* Updated Dictum dependency

## v0.1.0 (2023-11-09)
* Built initial implementation
