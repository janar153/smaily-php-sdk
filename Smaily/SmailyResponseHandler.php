<?php

namespace Smaily;


class SmailyResponseHandler {

    /**
     * Function to process Smaily API response
     *
     * @param $result
     * @return mixed
     * @access private
     */
    public function processRequest($result) {
        return json_decode($result, true);
    }
}