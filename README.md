# Synchronisation MantisBT

![GLPI Banner](https://user-images.githubusercontent.com/29282308/31666160-8ad74b1a-b34b-11e7-839b-043255af4f58.png)

[![License GPL 3.0](https://img.shields.io/badge/License-GPL%203.0-blue.svg)](https://github.com/pluginsGLPI/mantis/blob/develop/LICENSE.md)
[![Telegram GLPI](https://img.shields.io/badge/Telegram-GLPI-blue.svg)](https://t.me/glpien)
[![IRC Chat](https://img.shields.io/badge/IRC-%23GLPI-green.svg)](http://webchat.freenode.net/?channels=GLPI)
[![Follow Twitter](https://img.shields.io/badge/Twitter-GLPI%20Project-26A2FA.svg)](https://twitter.com/GLPI_PROJECT)
[![Project Status: Active](http://www.repostatus.org/badges/latest/active.svg)](http://www.repostatus.org/#active)
[![Conventional Commits](https://img.shields.io/badge/Conventional%20Commits-1.0.0-yellow.svg)](https://conventionalcommits.org)

Extend GLPI with Plugins.

## Table of Contents

* [Synopsis](#synopsis)
* [Build Status](#build-status)
* [Documentation](#documentation)
* [Versioning](#versioning)
* [Contact](#contact)
* [Professional Services](#professional-services)
* [Contribute](#contribute)
* [Copying](#copying)

## Synopsis

This plugin allows to synchronize tickets, problems and changes with the MantisBT tool.

### Features

* Connection via webservices of MantisBT (MantisConnect);
* Creation of a new MantisBT issue from a ticket, problem or GLPi change;
* Link an existing MantisBT issue from a ticket, problem or GLPi change;
* Transmission of information from the GLPi object to the created / linked MantisBT issue;
* Transfer and auto-update attachments of GLPi object to the MantisBT created / linked issue;
* Automatic update of GLPI object (transition to the resolved state) when the MantisBT issue is resolved;
* Management rights for the plugin;
* Configuration management of interconnection.

## Build Status

|**LTS**|**Bleeding Edge**|
|:---:|:---:|
|[![Travis CI build](https://api.travis-ci.org/pluginsGLPI/mantis.svg?branch=master)](https://travis-ci.org/pluginsGLPI/mantis/)|[![Travis CI build](https://api.travis-ci.org/pluginsGLPI/mantis.svg?branch=develop)](https://travis-ci.org/pluginsGLPI/mantis/)|

## Documentation

We maintain a detailed documentation of the project on the website, check the [How-tos](https://pluginsglpi.github.io/mantis/howtos/) and [Development](https://pluginsglpi.github.io/mantis/) section.

Only available in French in *docs* directory.

## Versioning

In order to provide transparency on our release cycle and to maintain backward compatibility, this project is maintained under [the Semantic Versioning guidelines](http://semver.org/). We are committed to following and complying with the rules, the best we can.

See [the tags section of our GitHub project](https://github.com/pluginsGLPI/mantis/tags/) for changelogs for each release version. Release announcement posts on [the official Teclib' blog](http://www.teclib-edition.com/en/communities/blog-posts/) contain summaries of the most noteworthy changes made in each release.

## Contact

For notices about major changes and general discussion of development, subscribe to the [/r/glpi](http://www.reddit.com/r/glpi) subreddit.
You can also chat with us via IRC in [#GLPI on freenode](http://webchat.freenode.net/?channels=GLPI) if you get stuck, and [@glpien on Telegram](https://t.me/glpien).

## Professional Services

The GLPI Network services are available through our [Partner's Network](http://www.teclib-edition.com/en/partners/). We provide special training, bug fixes with editor subscription, contributions for new features, and more.

Obtain a personalized service experience, associated with benefits and opportunities.

## Contribute

Want to file a bug, contribute some code, or improve documentation? Excellent! Read up on our
guidelines for [contributing](https://github.com/pluginsGLPI/mantis/blob/develop/.github/CONTRIBUTING.md) and then check out one of our issues in the [Issues Dashboard](https://github.com/pluginsGLPI/mantis/issues/).

## Copying

* **Name**: [GLPI](http://glpi-project.org/) is a registered trademark of [Teclib'](http://www.teclib-edition.com/en/).
* **Code**: you can redistribute it and/or modify it under the terms of the GNU General Public License ([GPLv3](https://www.gnu.org/licenses/gpl-3.0.en.html)).
* **Documentation**: released under Attribution 4.0 International ([CC BY 4.0](https://creativecommons.org/licenses/by/4.0/)).