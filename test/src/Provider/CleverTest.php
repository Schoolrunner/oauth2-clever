<?php

namespace Schoolrunner\OAuth2\Client\Test\Provider;

use Schoolrunner\OAuth2\Client\Provider\Clever as CleverProvider;

use Mockery as m;

class CleverTest extends \PHPUnit_Framework_TestCase
{
    protected $provider;

    protected function setUp()
    {
        $this->provider = new CleverProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
            'hostedDomain' => 'mock_domain',
            'accessType' => 'mock_access_type'
        ]);
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);
        
        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);

        $this->assertContains('read:students', $query['scope']);
        $this->assertContains('read:teachers', $query['scope']);
        $this->assertContains('read:user_id', $query['scope']);
        
        $this->assertAttributeNotEmpty('state', $this->provider);
    }

    public function testBaseAccessTokenUrl()
    {
        $url = $this->provider->getBaseAccessTokenUrl([]);
        $uri = parse_url($url);
        
        $this->assertEquals('/oauth/tokens', $uri['path']);
    }

    public function testResourceOwnerDetailsUrl()
    {
        $token = m::mock('League\OAuth2\Client\Token\AccessToken', [['access_token' => 'mock_access_token']]);
    
        $url = $this->provider->getResourceOwnerDetailsUrl($token);
        $uri = parse_url($url);
    
        $this->assertEquals('/me', $uri['path']);
        $this->assertNotContains('mock_access_token', $url);
    }

    public function testUserData()
    {
        $response = json_decode('{"type": "teacher", "data": {"id": "12345", "sis_id": "54321", "email": "mock_email", "name": {"first": "mock_first_name", "middle": "mock_middle_name", "last": "mock_last_name"}, "school": "1111122222", "district": "54545454"}}', true);

        $provider = m::mock('Schoolrunner\OAuth2\Client\Provider\Clever[fetchResourceOwnerDetails]')
            ->shouldAllowMockingProtectedMethods();
    
        $provider->shouldReceive('fetchResourceOwnerDetails')
            ->times(1)
            ->andReturn($response);
    
        $token = m::mock('League\OAuth2\Client\Token\AccessToken');
        $user = $provider->getResourceOwner($token);
        
        $this->assertInstanceOf('Schoolrunner\OAuth2\Client\Provider\CleverTeacher', $user);
    
        $this->assertEquals(12345, $user->getId());
        $this->assertEquals('mock_first_name mock_middle_name mock_last_name', $user->getName());
        $this->assertEquals('mock_first_name', $user->getFirstName());
        $this->assertEquals('mock_middle_name', $user->getMiddleName());
        $this->assertEquals('mock_last_name', $user->getLastName());
        $this->assertEquals('mock_email', $user->getEmail());
    
        $user = $user->toArray();
    
        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('sis_id', $user);
        $this->assertArrayHasKey('email', $user);
        $this->assertArrayHasKey('name', $user);
        $this->assertArrayHasKey('district', $user);
        $this->assertArrayHasKey('school', $user);
    }
    
    public function testUserDataUnknownUserType()
    {
        $response = json_decode('{"type": "district_admin", "data": {"id": "12345", "sis_id": "54321", "email": "mock_email", "name": {"first": "mock_first_name", "middle": "mock_middle_name", "last": "mock_last_name"}, "school": "1111122222", "district": "54545454"}}', true);

        $provider = m::mock('Schoolrunner\OAuth2\Client\Provider\Clever[fetchResourceOwnerDetails]')
            ->shouldAllowMockingProtectedMethods();
    
        $provider->shouldReceive('fetchResourceOwnerDetails')
            ->times(1)
            ->andReturn($response);
    
        $token = m::mock('League\OAuth2\Client\Token\AccessToken');
        $user = $provider->getResourceOwner($token);
        
        $this->assertInstanceOf('League\OAuth2\Client\Provider\ResourceOwnerInterface', $user);
        $this->assertInstanceOf('Schoolrunner\OAuth2\Client\Provider\CleverUser', $user);
    }
}