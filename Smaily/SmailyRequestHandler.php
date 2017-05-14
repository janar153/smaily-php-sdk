<?php

namespace Smaily;

use Smaily\SmailyResponseHandler as ResponseHandler;
use Smaily;

class SmailyRequestHandler extends Smaily {
    protected $responseHandler;
    protected $endpoint;
    protected $username;
    protected $password;
    protected $info;

    public function __construct($endpoint, $username, $password) {
        $this->endpoint = $endpoint;
        $this->username = $username;
        $this->password = $password;

        $this->responseHandler = new ResponseHandler();
    }

    /**
     * Function to get data from Smaily API
     *
     * @param $url
     * @param array $query
     * @return mixed
     * @throws \Exception
     * @access public
     */
    public function makeGetRequest($url, $query = array())  {
        $username = $this->username;
        $password = $this->password;

        $query = urldecode(http_build_query($query));
        $requestURL = $this->endpoint . $url . '?' . $query;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $requestURL);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // false for https
        curl_setopt($ch, CURLOPT_ENCODING, "gzip"); // the page encoding

        $result = curl_exec($ch);
        $this->info =  curl_getinfo($ch);

        if ($result === false) {
            $errorMessage = curl_error($ch);
            $errorCode = curl_errno($ch);
            throw new \Exception($errorMessage, $errorCode);
        }

        curl_close($ch);
        return $this->responseHandler->processRequest($result);
    }

    /**
     * Function to post data to Smaily API
     *
     * @param $url
     * @param $query
     * @return bool
     * @throws \Exception
     * @access public
     */
    public function makePostRequest($url, $query) {
        $username = $this->username;
        $password = $this->password;

        $requestURL = $this->endpoint . $url;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $requestURL);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
        curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // false for https
        curl_setopt($ch, CURLOPT_ENCODING, "gzip"); // the page encoding

        $result = curl_exec($ch);
        $this->info =  curl_getinfo($ch);
        if ($result === false) {
            $errorMessage = curl_error($ch);
            $errorCode = curl_errno($ch);
            throw new \Exception($errorMessage, $errorCode);
        }
        curl_close($ch);
        $result = $this->responseHandler->processRequest($result);
        if (!isset($result['code'])) {
            throw new \Exception("Something went wrong with the request.", $result['code']);
        }
        else {
            return $result;
        }
    }

    public function getLastInfo() {
        return $this->info;
    }
}