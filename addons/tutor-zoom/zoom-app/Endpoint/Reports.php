<?php

/**
 * @copyright  https://github.com/UsabilityDynamics/zoom-api-php-client/blob/master/LICENSE
 */
namespace Zoom\Endpoint;

use Zoom\Interfaces\Request;

/**
 * Class Reports
 * @package Zoom\Interfaces
 */
class Reports extends Request {

    /**
     * Meetings constructor.
     * @param $apiKey
     * @param $apiSecret
     */
    public function __construct($apiKey, $apiSecret) {
        parent::__construct($apiKey, $apiSecret);
    }

    /**
     * Meeting Participants
     *
     * @param $meetingUUID
     * @param array $query
     * @return array|mixed
     */
    public function meetingParticipants(string $meetingUUID, array $query = []) {
        return $this->get("report/meetings/{$meetingUUID}/participants", $query);
    }

    public function dailyReports(array $query = []) {
        return $this->get("report/daily/", $query);
    }

}