# Solid Client PHP

> Re-decentralizing the web

[Solid](https://solidproject.org) (derived from "**so**cial **li**nked **d**ata") is a proposed set of
conventions and tools for building *decentralized Web applications* based on
[Linked Data](http://www.w3.org/DesignIssues/LinkedData.html) principles.

This repository contains a PHP library for accessing data and managing permissions on data stored in a Solid Pod
It also a contains a Symfony bundle to easily build Solid applications with the [Symfony](https://symfony.com) and [API Platform](https://api-platform.com) frameworks.

## Install

```
composer require dunglas/solid-client-php
```

If you use [Symfony](https://symfony.com) or [API Platform](https://api-platform.com),
the bundle and the corresponding recipe will be installed automatically.

## Example

```php
<?php

use Dunglas\PhpSolidClient\SolidClientFactory;
use Dunglas\PhpSolidClient\OidcClient;
use Symfony\Component\HttpClient\HttpClient;

$solidClientFactory = new SolidClientFactory(HttpClient::create());

// Create an anonymous Solid client
$anonymousSolidClient = $solidClientFactory->create();

// Fetch the WebID profile of an user
$profile = $anonymousSolidClient->getProfile('https://example.com/your/webid');
// Fetch the OIDC issuer for an user
$oidcIssuer = $anonymousSolidClient->getOidcIssuer('https://example.com/your/webid');

// Create a Solid OIDC client for this user
$oidcClient = new OidcClient($oidcIssuer);
// Register the OIDC client dynamically
$oidcClient->register();
// Authenticate the user
$oidcClient->authenticate();
// At this point you may want to save $oidcClient in the session
// The user will be redirected to the OIDC server to log in

// Create a Solid client generating DPoP access tokens for the logged-in user
$loggedSolidClient = $solidClientFactory->create($oidcClient);

// Create a new container
$containerResponse = $loggedSolidClient->createContainer('https://mypod.example.com', 'blog');
$container = $containerResponse->getContent();

// Post a new note
$apiPlatformResponse = $loggedSolidClient->post('https://mypod.example.com/blog', 'api-platform-conference', <<<TTL
@prefix as: <http://www.w3.org/ns/activitystreams#>.

<> a as:Note; as:content "Going to API Platform Conference".
TTL
);
$apiPlatformCon = $apiPlatformResponse->getContent();

// Fetch an existing note
$symfonyLiveResponse = $loggedSolidClient->get('https://mypod.example.com/blog/symfony-live');
$symfonyLive = $symfonyLiveResponse->getContent();

// Logout
$oidcClient->signOut($oidcClient->getIdToken());
```

## Features

* Standalone PHP library
* Symfony Bundle
  * OAuth/OIDC authenticator
  * Solid client as a service

### Authentication

* Modern and Fully featured [OAuth](https://datatracker.ietf.org/doc/html/rfc6749) and [OpenID Connect](https://openid.net/connect/) client (work even without Solid, extends [`jumbojett/openid-connect-php`](https://github.com/jumbojett/OpenID-Connect-PHP))
  * [Elliptic Curve Digital Signature Algorithm (ECDSA)](https://en.wikipedia.org/wiki/Elliptic_Curve_Digital_Signature_Algorithm) JWT/JWK keys (uses [JWT Framework](https://web-token.spomky-labs.com))
  * [Demonstrating Proof-of-Possession at the Application Layer (DPoP)](https://datatracker.ietf.org/doc/html/draft-ietf-oauth-dpop)
* [Solid OIDC](https://solid.github.io/solid-oidc/primer/)

### Identity

* [WebID](https://www.w3.org/2005/Incubator/webid/spec/identity/)
* [Solid WebID Profiles](https://github.com/solid/solid-spec/blob/master/solid-webid-profiles.md)

### Reading and Writing Resources

* [Linked Data Platform](https://www.w3.org/TR/ldp/)
* [Solid HTTPS REST API](https://github.com/solid/solid-spec/blob/master/api-rest.md) (uses [Symfony HttpClient](https://symfony.com/doc/current/http_client.html))
* [Solid Content Representation](https://github.com/solid/solid-spec/blob/master/content-representation.md) (delegated to [EasyRDF](https://www.easyrdf.org/))

## Not Implemented Yet

* [OAuth Client ID](https://solid.github.io/solid-oidc/primer/#authorization-code-pkce-flow-step-7)
* [Solid OIDC "Request Flow"](https://solid.github.io/solid-oidc/primer/#request-flow) (currently not supported by mainstream Solid servers)
* [Web Access Control](https://solidproject.org/TR/wac)
* [WebSockets API](https://github.com/solid/solid-spec#websockets-api=)
* [Social Web App Protocols](https://github.com/solid/solid-spec#social-web-app-protocols)
* [WebID-TLS](https://github.com/solid/solid-spec#webid-tls=) (not supported anymore in web browsers)
* Symfony Bundle
  * Redirect after login
