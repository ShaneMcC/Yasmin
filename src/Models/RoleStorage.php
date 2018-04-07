<?php
/**
 * Yasmin
 * Copyright 2017-2018 Charlotte Dunois, All Rights Reserved
 *
 * Website: https://charuru.moe
 * License: https://github.com/CharlotteDunois/Yasmin/blob/master/LICENSE
*/

namespace CharlotteDunois\Yasmin\Models;

/**
 * Role Storage to store a guild's roles, utilizes Collection.
 */
class RoleStorage extends Storage {
    protected $guildID;
    
    /**
     * @internal
     */
    function __construct(\CharlotteDunois\Yasmin\Client $client, \CharlotteDunois\Yasmin\Models\Guild $guild, array $data = null) {
        parent::__construct($client, $data);
        $this->guildID = $guild->id;
    }
    
    /**
     * Resolves given data to a Role.
     * @param \CharlotteDunois\Yasmin\Models\Role|string|int  $role  string/int = role ID
     * @return \CharlotteDunois\Yasmin\Models\Role
     * @throws \InvalidArgumentException
     */
    function resolve($role) {
        if($role instanceof \CharlotteDunois\Yasmin\Models\Role) {
            return $role;
        }
        
        if(\is_int($role)) {
            $role = (string) $role;
        }
        
        if(\is_string($role) && $this->has($role)) {
            return $this->get($role);
        }
        
        throw new \InvalidArgumentException('Unable to resolve unknown role');
    }
    
    /**
     * Returns the item for a given key. If the key does not exist, null is returned.
     * @param mixed  $key
     * @return \CharlotteDunois\Yasmin\Models\Role|null
     */
    function get($key) {
        return parent::get($key);
    }
    
    /**
     * @internal
     */
    function factory(array $data, ?\CharlotteDunois\Yasmin\Models\Guild $guild = null) {
        if($this->has($data['id'])) {
            $role = $this->get($data['id']);
            $role->_patch($data);
            return $role;
        }
        
        $role = new \CharlotteDunois\Yasmin\Models\Role($this->client, ($guild ?? $this->guild), $data);
        $this->set($role->id, $role);
        return $role;
    }
}
