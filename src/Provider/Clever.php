<?php
namespace Schoolrunner\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use League\OAuth2\Client\Provider\AbstractProvider;
use Psr\Http\Message\ResponseInterface;

/**
 * Clever OAuth2 Client
 */
class Clever extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * Get base authorization url
     * 
     * @return string Authorization url
     */
    public function getBaseAuthorizationUrl()
    {
        return 'https://clever.com/oauth/authorize';
    }

    /**
     * Get base access token url
     * 
     * @return string Access token url
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://clever.com/oauth/tokens';
    }
    
    /**
     * Get resource owner url
     * 
     * @return string Resource owner url
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://api.clever.com/me';
    }
    
    /**
     * Get requested scopes
     * 
     * @return array Scopes
     */
    protected function getDefaultScopes()
    {
        return [
            'read:students',
            'read:teachers',
            'read:user_id'
        ];
    }
    
    /**
     * Add Authorization header to Default headers
     * 
     * @return array Default Headers
     */
    protected function getDefaultHeaders()
    {
        $auth = $this->clientId . ':' . $this->clientSecret;
        
        return [
            'Authorization' => 'Basic ' . base64_encode($auth)
        ];
    }
    
    /**
     * Check response for errors
     * 
     * @param  ResponseInterface $response Response object
     * @param  array             $data     Error Data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() != 200)
        {
            $data = (is_array($data)) ? $data : json_decode($data, true);
            throw new IdentityProviderException($data['error_description'], $response->getStatusCode(), $data);
        }
    }
    
    /**
     * Create resource owner based on response
     * 
     * @param  array       $response Response data
     * @param  AccessToken $token    Access token
     * @return object                Resource owner
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        $base = 'Schoolrunner\\OAuth2\\Client\\Provider\\Clever';
        
        $userClass = $base . ucfirst($response['type']);
        
        // if the type we get back doesnt have a class use generic user class
        if (!class_exists($userClass))
        {
            $userClass = $base . 'User';
        }
        
        return new $userClass($response);
    }
}
