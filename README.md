## About
PHPDiff is diff/merge library written in PHP which supports both two and three-way merging. This library is currently in
the alpha stage.

**Features**:
 - Two-way diffing/merging
 - Three-way diffing/merging
 	- Using Weave merge algorithm
 	- Using Three-way merge algorithm
 - Custom comparison implementations

You can find the full feature list and the changes made to this library in the [changelog](CHANGELOG.md).

## Requirements
PHP 7.1.0 or higher is required (although it should run on 5.4 as well probably).

## Installation
You may install PHPDiff with composer by running the following commands in your console:
```
php composer.phar require chita/phpdiff:2.*
```

## Documentation
For examples and documentation you may wish to read the [docs](http://phpdiff.readthedocs.io/en/latest/).

## Versioning
PHPDiff uses [semantic versioning](http://semver.org/).

## Tests
PHPDiff uses automated tests to prevent code regressions.

| v2.0: ![v2.0](https://github.com/CHItA/TopologicalSort/workflows/Tests/badge.svg?branch=v1.0) | master ![master](https://github.com/CHItA/TopologicalSort/workflows/Tests/badge.svg?branch=master) |
| --- | --- |

## License
PHPDiff is available under the [MIT License](LICENSE).

## Contributions
If you have some code you would like to contribute to the project, please note that this project uses the PSR-2 coding
standard, and the PSR-4 autoloading standard.

Contributions to the documentation, tests or to the code, or simply reporting issues here are welcomed, and very much
appreciated.
