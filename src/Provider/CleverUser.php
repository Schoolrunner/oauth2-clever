<?php 
namespace Schoolrunner\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * Clever User object
 */
class CleverUser implements ResourceOwnerInterface
{
    /**
     * Clever user data
     * 
     * @var array
     */
    private $data;
    
    /**
     * Create Clever user
     * 
     * @param $data Clever user data
     */
    public function __construct($data) 
    {
        $this->data = $data;
    }
    
    /**
     * Get Id
     * 
     * @return string ID
     */
    public function getId()
    {
        return $this->data['data']['id'];
    }
    
    /**
     * Get perferred display name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getFirstName() . ' ' . $this->getMiddleName() . ' ' . $this->getLastName(); 
    }

    /**
     * Get perferred first name.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->data['data']['name']['first'];
    }
    
    /**
     * Get perferred middle name.
     *
     * @return string
     */
    public function getMiddleName()
    {
        return $this->data['data']['name']['middle'];
    }

    /**
     * Get perferred last name.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->data['data']['name']['last'];
    }
    
    /**
     * Get Clever user email address
     * 
     * @return string Email address
     */
    public function getEmail()
    {
        return $this->data['data']['email'];
    }
    
    /**
     * Get Clever user SIS id
     * 
     * @return string sis ID
     */
    public function getSisId()
    {
        return $this->data['data']['sis_id'];
    }
    
    /**
     * Get user data as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data['data'];
    }
}