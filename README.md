ElasticPress Stream
===========================

Use ElasticPress to power [Stream](https://wordpress.org/plugins/stream/) with Elasticsearch.

## Background

Stream is a WordPress plugin that logs user activity. Every logged-in user action is displayed in an activity stream and organised for easy filtering by User, Role, Context, Action or IP address. Stream is a powerful tool for editorial teams, providing audit trails for potential mistakes and even security breaches.

The core Stream plugin stores data in MySQL which after awhile can became bloated and slow. ElasticPress Stream let's you store data in Elasticsearch which is faster as well as off-site which is more secure.

## Requirements

* Stream plugin
* Elasticsearch 5.0+
* ElasticPress 2.2+
* PHP 5.6+

## Setup

1. Install Stream. For now you will need to use the `develop` branch on [Github](https://github.com/xwp/stream).
2. Install Elasticsearch and [ElasticPress](https://wordpress.org/plugins/elasticpress/)
3. Install ElasticPress Stream. Within the ElasticPress admin dashboard, activate the ElasticPress Stream feature.

Once ElasticPress Stream is act

## Issues

If you identify any errors or have an idea for improving the plugin, please [open an issue](https://github.com/10up/elasticpress-autosuggest/issues). We're excited to see what the community thinks of this project, and we would love your input!

## License

ElasticPress Stream is free software; you can redistribute it and/or modify it under the terms of the [GNU General Public License](http://www.gnu.org/licenses/gpl-2.0.html) as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
