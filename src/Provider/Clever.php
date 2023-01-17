<?php
namespace Schoolrunner\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use League\OAuth2\Client\Provider\AbstractProvider;
use Psr\Http\Message\ResponseInterface;
use Schoolrunner\OAuth2\Client\User\UserFactory;

/**
 * Clever OAuth2 Client
 */
class Clever extends AbstractProvider
{
    use BearerAuthorizationTrait;
    
    /**
     * @var UserFactory
     */
    protected $userFactory;
    
    /**
     * Constructs an OAuth 2.0 service provider.
     *
     * Override to add UserFactory
     *
     * @param array $options An array of options to set on this provider.
     *     Options include `clientId`, `clientSecret`, `redirectUri`, and `state`.
     *     Individual providers may introduce more options, as needed.
     * @param array $collaborators An array of collaborators that may be used to
     *     override this provider's default behavior. Collaborators include
     *     `grantFactory`, `requestFactory`, `httpClient`, and `randomFactory`.
     *     Individual providers may introduce more collaborators, as needed.
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);
        
        if (empty($collaborators['userFactory'])) {
            $collaborators['userFactory'] = new UserFactory();
        }
        $this->setUserFactory($collaborators['userFactory']);
    }
    
    /**
     * Sets the instance of the user factory.
     *
     * @param  UserFactory $factory
     * @return self
     */
    public function setUserFactory(UserFactory $factory)
    {
        $this->userFactory = $factory;

        return $this;
    }

    /**
     * Returns the current user factory instance.
     *
     * @return UserFactory
     */
    public function getUserFactory()
    {
        return $this->userFactory;
    }
    
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
        $request = parent::getAuthenticatedRequest('GET', 'https://api.clever.com/me', $token);

        $response = parent::getParsedResponse($request);
       
        $canonicalUri;

        foreach ($response['links'] as $link) {

          if ($link['rel'] === 'canonical') {
            $canonicalUri = $link['uri'];
          }

        };

        return 'https://api.clever.com' . $canonicalUri;
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
     * Get Access Token Options
     * 
     * Override to add a Basic Auth header needed when 
     * requesting an access token from Clever
     *
     * @param  array $params Access token params
     * @return array Default Headers
     */
     protected function getAccessTokenOptions(array $params)
     {
         $options = parent::getAccessTokenOptions($params);
         $options['headers']['authorization'] = 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret);

         return $options;
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
        if ($response->getStatusCode() >= 400)
        {
            $data = (is_array($data)) ? $data : json_decode($data, true);
            throw new IdentityProviderException($data['error'], $response->getStatusCode(), $data);
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
        $selfUri = $response['links'][0]['uri'];

        $typeString = array_slice(explode('/', $selfUri), 2)[0];

        $className = $this
            ->getUserFactory()
            ->getClassNameForUserType(rtrim($typeString, 's'));

        return new $className($response, $token);
    }
}
