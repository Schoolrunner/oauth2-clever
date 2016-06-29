# Clever Provider for OAuth 2.0 Client

[![License](https://img.shields.io/packagist/l/schoolrunner/oauth2-clever.svg)](https://github.com/schoolrunner/oauth2-cleover/blob/master/LICENSE)
[![Latest Stable Version](https://img.shields.io/packagist/v/schoolrunner/oauth2-clever.svg)](https://packagist.org/packages/schoolrunner/oauth2-clever)

This package provides [Clever OAuth 2.0](https://dev.clever.com/instant-login/bearer-tokens) support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require schoolrunner/oauth2-clever
```

## Usage

Usage is the same as The League's OAuth client, using `Schoolrunner\OAuth2\Client\Provider\Clever` as the provider.

### Authorization Code Flow

```php
$provider = new Schoolrunner\OAuth2\Client\Provider\Clever([
    'clientId'     => '{clever-client-id}',
    'clientSecret' => '{clever-client-secret}',
    'redirectUri'  => 'https://example.com/callback-url'
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->state;
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $userDetails = $provider->getUserDetails($token);

        // Use these details to create a new profile
        printf('Hello %s!', $userDetails->getName());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->accessToken;

}
```

## Refreshing a Token

Clever does not use refresh tokens.
