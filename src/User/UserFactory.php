<?php 
namespace Schoolrunner\OAuth2\Client\User;

/**
 * Default User Factory
 */
class UserFactory
{
    /**
     * Base Class Name
     */
    const USER_CLASS_NAME_BASE = 'Schoolrunner\\OAuth2\\Client\\User';

    /**
     * Generic User class
     */
    const USER_CLASS_NAME_GENERIC = 'Schoolrunner\\OAuth2\\Client\\User\\CleverUser';

    /**
     * Get Class Name based on type passed in
     * 
     * @param  string $type User type
     * @return string       Classname
     */
    public function getClassNameForUserType($type)
    {
        $className = $this->getTypeUserClass($type);

        if (!class_exists($className))
        {
            $className = $this->getGenericUserClass();
        }

        return $className;
    }

    /**
     * Return dynamic Class
     * @param  string $type User type
     * @return string       Classname
     */
    public function getTypeUserClass($type)
    {
        return self::USER_CLASS_NAME_BASE . '\\' . 'Clever' . ucfirst($type);
    }

    /**
     * Get generic user class name
     * 
     * @return string       Classname
     */
    public function getGenericUserClass()
    {
        return self::USER_CLASS_NAME_GENERIC;
    }
}