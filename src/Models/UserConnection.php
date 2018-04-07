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
 * Represents an user connection.
 *
 * @property string                               $id                 The ID of the connection account.
 * @property string                               $name               The username of the connection account.
 * @property string                               $type               The type of the user connection (e.g. twitch, youtube).
 * @property bool                                 $revoked            Whether the connection is revoked.
 *
 * @property \CharlotteDunois\Yasmin\Models\User  $user               The user which this user connection belongs to, or null.
 */
class UserConnection extends ClientBase {
    protected $userID;
    
    protected $id;
    protected $name;
    protected $type;
    protected $revoked;
    
    /**
     * @internal
     */
    function __construct(\CharlotteDunois\Yasmin\Client $client, \CharlotteDunois\Yasmin\Models\User $user, array $connection) {
        parent::__construct($client);
        $this->userID = $user->id;
        
        $this->id = $connection['id'];
        $this->name = $connection['name'];
        $this->type = $connection['type'];
        $this->revoked = $connection['revoked'];
    }
    
    function __get($name) {
        if(\property_exists($this, $name)) {
            return $this->$name;
        }
        
        if($name === 'user') {
            return $this->client->users->get($this->userID);
        }
        
        return parent::__get($name);
    }
}
