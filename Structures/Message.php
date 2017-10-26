<?php
/**
 * Yasmin
 * Copyright 2017 Charlotte Dunois, All Rights Reserved
 *
 * Website: https://charuru.moe
 * License: https://github.com/CharlotteDunois/Yasmin/blob/master/LICENSE
*/

namespace CharlotteDunois\Yasmin\Structures;

/**
 * Represents a received message.
 */
class Message extends Structure { //TODO: Implementation
    protected $id;
    protected $author;
    protected $channel;
    protected $content;
    protected $createdTimestamp;
    protected $editedTimestamp;
    protected $tts;
    protected $mentionEveryone;
    protected $nonce;
    protected $pinned;
    protected $system;
    
    protected $attachments;
    protected $cleanContent;
    protected $embeds = array();
    protected $mentions;
    protected $reactions;
    
    /**
     * @access private
     */
    function __construct(\CharlotteDunois\Yasmin\Client $client, \CharlotteDunois\Yasmin\Interfaces\TextChannelInterface $channel, array $message) {
        parent::__construct($client);
        
        $this->id = $message['id'];
        $this->author = (empty($message['webhook_id']) ? $client->users->patch($message['author']) : new \CharlotteDunois\Yasmin\Structures\User($client, $message['author'], true));
        $this->channel = $channel;
        $this->content = $message['content'];
        $this->createdTimestamp = (new \DateTime($message['timestamp']))->format('U');
        $this->editedTimestamp = (!empty($message['edited_timestamp']) ? (new \DateTime($message['edited_timestamp']))->format('U') : null);
        $this->tts = (bool) $message['tts'];
        $this->mentionEveryone = (bool) $message['mention_everyone'];
        $this->nonce = (!empty($message['nonce']) ? $message['nonce'] : null);
        $this->pinned = (bool) $message['pinned'];
        $this->system = ($message['type'] > 0 ? true : false);
        
        $this->author->lastMessageID = $message['id'];
        
        $this->attachments = new \CharlotteDunois\Yasmin\Structures\Collection();
        foreach($message['attachments'] as $attachment) {
            $this->attachments->set($attachment['id'], (new \CharlotteDunois\Yasmin\Structures\MessageAttachment($attachment)));
        }
        
        foreach($message['embeds'] as $embed) {
            $this->embeds[] = new \CharlotteDunois\Yasmin\Structures\MessageEmbed($embed);
        }
        
        $this->mentions = array(
            'channels' => (new \CharlotteDunois\Yasmin\Structures\Collection()),
            'members' => (new \CharlotteDunois\Yasmin\Structures\Collection()),
            'roles' => (new \CharlotteDunois\Yasmin\Structures\Collection()),
            'users' => (new \CharlotteDunois\Yasmin\Structures\Collection())
        );
        
        $guild = $channel->guild;
        $this->cleanContent = $this->content;
        
        \preg_match_all('/<#(\d+)>/', $this->content, $matches);
        if(!empty($matches[1])) {
            foreach($matches[1] as $match) {
                $channel = $this->client->channels->get($match);
                if($channel) {
                    $this->cleanContent = \str_replace($channel->__toString(), $channel->name, $this->cleanContent);
                }
            }
        }
        
        if(!empty($message['mentions'])) {
            foreach($message['mentions'] as $mention) {
                $user = $this->client->users->patch($mention);
                if($user) {
                    $member = null;
                    
                    $this->mentions['users']->set($mention['id'], $user);
                    if($guild) {
                        $member = $guild->members->get($mention['id']);
                        $this->mentions['members']->set($mention['id'], $member);
                    }
                    
                    $this->cleanContent = \str_replace($user->__toString(), ($guild ? $member->displayName : $user->username), $this->cleanContent);
                }
            }
        }
        
        
        if($guild && !empty($message['mentions_role'])) {
            foreach($message['mentions_role'] as $mention) {
                $role = $guild->roles->get($mention['id']);
                if($role) {
                    $this->mentions['roles']->set($mention['id'], $role);
                    $this->cleanContent = \str_replace($role->__toString(), $role->name, $this->cleanContent);
                }
            }
        }
        
        $this->reactions = new \CharlotteDunois\Yasmin\Structures\Collection();
        if(!empty($message['reactions'])) {
            foreach($message['reactions'] as $reaction) {
                $emoji = ($client->emojis->get($reaction['emoji']['id'] ?? $reaction['emoji']['name']) ?? (new \CharlotteDunois\Yasmin\Structures\Emoji($client, $this->channel->guild, $reaction['emoji'])));
                $this->reactions->set($emoji->id, (new \CharlotteDunois\Yasmin\Structures\MessageReaction($client, $this, $emoji, $reaction)));
            }
        }
    }
    
    /**
     * @property-read string                                                              $id                 The message ID.
     * @property-read \CharlotteDunois\Yasmin\Structures\User                             $author             The user that created the message.
     * @property-read \CharlotteDunois\Yasmin\Interfaces\TextChannelInterface             $channel            The channel this message was created in.
     * @property-read int                                                                 $createdTimestamp   The timestamp of when this message was created.
     *
     * @property-read \DateTime                                                           $createdAt          An DateTime object of the createdTimestamp.
     * @property-read \CharlotteDunois\Yasmin\structures\Guild|null                       $guild              The correspondending guild (if message posted in a guild).
     * @property-read \CharlotteDunois\Yasmin\structures\GuildMember|null                 $member              The correspondending guildmember of the author (if message posted in a guild).
     */
    function __get($name) {
        if(\property_exists($this, $name)) {
            return $this->$name;
        }
        
        switch($name) {
            case 'createdAt':
                return (new \DateTime('@'.$this->createdTimestamp));
            break;
            case 'guild':
                return $this->channel->guild;
            break;
            case 'member':
                if($this->channel->guild) {
                    return $this->channel->guild->members->get($this->author->id);
                }
            break;
            case 'type':
                return $this->channel->type;
            break;
        }
        
        return null;
    }
    
    function edit(array $data) {
        
    }
    
    function delete(string $reason) {
        
    }
    
    /**
     * Automatically converts to a mention.
     */
    function __toString() {
        if($this->requireColons === false) {
            return $this->name;
        }
        
        return '<:'.$this->name.':'.$this->id.'>';
    }
}
