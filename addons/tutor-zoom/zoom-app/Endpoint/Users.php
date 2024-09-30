<?php

/**
 * @copyright  https://github.com/UsabilityDynamics/zoom-api-php-client/blob/master/LICENSE
 */
namespace Zoom\Endpoint;

use Zoom\Interfaces\Request;

/**
 * Class Users
 * @package Zoom\Interfaces
 */
class Users extends Request {

    /**
     * Users constructor.
     * @param $apiKey
     * @param $apiSecret
     */
    public function __construct($apiKey, $apiSecret) {
        parent::__construct($apiKey, $apiSecret);
    }

    /**
     * List
     *
     * @param array $query
     * @return array|mixed
     */
    public function userlist( array $query = [] ) {
        return $this->get( "users", $query );
    }

    /**
     * Create
     *
     * @param array|null $data
     * @return array|mixed
     */
    public function create( array $data  = null ) {
        return $this->post( "users", $data );
    }

    /**
     * Retrieve
     *
     * @param $userID
     * @param array $query
     * @return array|mixed
     */
    public function retrieve( string $userID, array $query = [] ) {
        return $this->get( "users/{$userID}", $query );
    }

    /**
     * Remove
     *
     * @param $userId
     * @return array|mixed
     */
    public function remove( string $userId ) {
        return $this->delete( "users/{$userId}" );
    }

    /**
     * Update
     *
     * @param $userId
     * @param array $data
     * @return array|mixed
     */
    public function update( string $userId, array $data = [] ) {
        return $this->patch( "users/{$userId}", $data );
    }
}