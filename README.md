# ElasticPress Stream

> Use ElasticPress to power [Stream](https://wordpress.org/plugins/stream/) with Elasticsearch.

[![Support Level](https://img.shields.io/badge/support-stable-blue.svg)](#support-level) [![GPLv2 License](https://img.shields.io/github/license/10up/elasticpress-stream.svg)](https://github.com/10up/elasticpress-stream/blob/develop/LICENSE.md)

## Background

Stream is a WordPress plugin that logs user activity. Every logged-in user action is displayed in an activity stream and organized for easy filtering by User, Role, Context, Action or IP address. Stream is a powerful tool for editorial teams, providing audit trails for potential mistakes and even security breaches.

The core Stream plugin stores data in MySQL which after awhile can became bloated and slow. ElasticPress Stream let's you store data in Elasticsearch which is faster as well as off-site which is more secure.

## Requirements

* Stream plugin
* Elasticsearch 5.0+
* ElasticPress 2.2+
* PHP 5.6+

## Setup

1. Install Stream. For now you will need to use the `develop` branch on [Github](https://github.com/xwp/stream).
2. Install [Elasticsearch](https://www.elastic.co/products/elasticsearch) and [ElasticPress](https://wordpress.org/plugins/elasticpress/)
3. Install ElasticPress Stream. Within the ElasticPress admin dashboard, activate the ElasticPress Stream feature.

Once the ElasticPress Stream feature has been activated, Stream will start using Elasticsearch instead of MySQL. There are no settings to configure.

*Note: Be sure to consider the security and privacy implications of where detailed site log data is stored. Elasticsearch Stream indices requires POST, PUT, and GET requests properly configured to shield access and tampering from the public.*

## Support Level

**Stable:** 10up is not planning to develop any new features for this, but will still respond to bug reports and security concerns. We welcome PRs, but any that include new features should be small and easy to integrate and should not include breaking changes. We otherwise intend to keep this tested up to the most recent version of WordPress.

## Like what you see?

<a href="http://10up.com/contact/"><img src="https://10up.com/uploads/2016/10/10up-Github-Banner.png" width="850" alt="Work with us at 10up"></a>
