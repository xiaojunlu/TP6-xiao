<?php

namespace app\biz\user;

// use app\biz\role\util\PermissionBuilder;

class CurrentUser implements \ArrayAccess, \Serializable
{
    protected $data;
    protected $permissions;

    protected $context = array();

    public function serialize()
    {
        return serialize($this->data);
    }

    public function unserialize($serialized)
    {
        $this->data = unserialize($serialized);
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;

        return $this;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        throw new \RuntimeException("{$name} is not exist in CurrentUser.");
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    public function clearNotifacationNum()
    {
        $this->data['newNotificationNum'] = '0';
    }

    public function clearMessageNum()
    {
        $this->data['newMessageNum'] = '0';
    }

    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function getId()
    {
        return $this->id;
    }

    public function eraseCredentials()
    { }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return !$this->locked;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return true;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function isEqualTo(UserInterface $user)
    {
        if ($this->email !== $user->getUsername()) {
            return false;
        }

        if (array_diff($this->roles, $user->getRoles())) {
            return false;
        }

        if (array_diff($user->getRoles(), $this->roles)) {
            return false;
        }

        return true;
    }

    public function isLogin()
    {
        return empty($this->id) ? false : true;
    }

    public function isAdmin()
    {
        $permissions = $this->getPermissions();

        return !empty($permissions) && array_key_exists('admin', $permissions);
    }

    public function isSuperAdmin()
    {
        if (count(array_intersect($this->getRoles(), array('ROLE_SUPER_ADMIN'))) > 0) {
            return true;
        }

        return false;
    }

    public function isTeacher()
    {
        $permissions = $this->getPermissions();

        $isTeacher = (in_array('admin_train_teach_manage_my_teaching_courses_manage', array_keys($permissions))
            or in_array('admin_train_teach_manage_my_teaching_classrooms_manage', array_keys($permissions))
            or in_array('admin_train_teach_manage_project_plan_teaching_manage', array_keys($permissions)));

        return $isTeacher;
    }

    /**
     * 是否是机构账号
     *
     * @return boolean
     */
    public function isOrg()
    {
        if (count(array_intersect($this->getRoles(), array('ROLE_ORG'))) > 0) {
            return true;
        }

        return false;
    }

    public function fromArray(array $user)
    {
        $this->data = $user;

        return $this;
    }

    public function toArray()
    {
        return $this->data;
    }

    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function setContext($name, $value)
    {
        $this->context[$name] = $value;
    }

    public function getContext($name)
    {
        return isset($this->context[$name]) ? $this->context[$name] : null;
    }

    /**
     * @param string $code 权限编码
     *
     * @return bool
     */
    public function hasPermission($code)
    {
        $currentUserPermissions = $this->getPermissions();

        if (!empty($currentUserPermissions[$code])) {
            return true;
        }

        $tree = PermissionBuilder::instance()->findAllPermissionTree(true);
        $codeTree = $tree->find(function ($tree) use ($code) {
            return $tree->data['code'] === $code;
        });

        if (empty($codeTree)) {
            return false;
        }

        $disableTree = $codeTree->findToParent(function ($parent) {
            return isset($parent->data['disable']) && (bool) $parent->data['disable'];
        });

        if (is_null($disableTree)) {
            return false;
        }

        $parent = $disableTree->getParent();

        if (is_null($parent)) {
            return false;
        }

        if (empty($parent->data['parent'])) {
            return true;
        } else {
            return !empty($currentUserPermissions[$parent->data['code']]);
        }
    }
}
