<?php 
namespace Schoolrunner\OAuth2\Client\User;

class UserFactory
{
    const USER_CLASS_NAME_BASE = 'Schoolrunner\\OAuth2\\Client\\User';
    const USER_CLASS_NAME_GENERIC = 'Schoolrunner\\OAuth2\\Client\\User\\CleverUser';
    
    public function getClassNameForUserType($type)
    {
        $className = $this->getTypeUserClass($type);
        
        if (!class_exists($className))
        {
            $className = $this->getGenericUserClass();
        }
        
        return $className;
    }
    
    public function getTypeUserClass($type)
    {
        return self::USER_CLASS_NAME_BASE . '\\' . 'Clever' . ucfirst($type);
    }
    
    public function getGenericUserClass()
    {
        return self::USER_CLASS_NAME_GENERIC;
    }
}