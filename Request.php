<?php
class Request {
    protected $url_host;
    protected $url_port;
    protected $url_path;
    protected $proxy_host;
    protected $proxy_port;

    protected $headers = array();

    protected $nl = "\r\n";

    function __construct($url_host, $url_port, $url_path) {
        $this->url_host = $url_host;
        $this->url_port = $url_port;
        $this->url_path = $url_path;

        $proxy_host = null;
        $proxy_port = null;

        if (is_null($proxy_host)) {
            $proxy_host = $url_host;
            $proxy_port = $url_port;
        }

        $this->proxy_host = $proxy_host;
        $this->proxy_port = $proxy_port;
    }



    // "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8" . $this->nl .
    // "Accept-Language: en-US,en;q=0.5" . $this->nl .
    // "Accept-Encoding: gzip, deflate" . $this->nl .
    // "Referer: http://".$hostport."" . $this->nl .
    // "Cache-Control: max-age=0" . $this->nl .
    // "Accept: text/xml" . $this->nl .
    // "Accept-Charset: UTF-8" . $this->nl .


    function header($headerName, $headerValue) {
        $this->headers[$headerName] = $headerValue;
    }

    function getExtraHeaders() {
        $headers = "";
        foreach ($this->headers as $headerName => $headerValue) {
            $headers .= "$headerName: $headerValue" . $this->nl;
        }
        return $headers;
    }

    function post($data) {
        $hostport = $this->url_host . (!empty($this->url_port) ? (":".$this->url_port) : "");

        $response = null;

        $fp = @fsockopen($this->proxy_host, $this->proxy_port, $errno, $errstr, 10);
        $responseBody = "";

        if ($fp) {

            $headers = "POST http://".$hostport.$this->url_path." HTTP/1.1" . $this->nl .
                       "Host: ".$hostport."\r\n";
            $headers .= "Content-Type: text/xml; charset=UTF-8" . $this->nl;

            $headers .= $this->getExtraHeaders();

            $headers .= "Content-Length: ".strlen($data) . $this->nl;
            $headers .= $this->nl;

            // echo $headers;

            fputs($fp,$headers.$data);

            $response = "";
            $responseHeaders = "";
            while(!feof($fp)) {
                $resp = fread($fp,1024);
                $response .= $resp;
            }

            $responseParts = explode("\r\n\r\n", $response);
            // var_dump($responseParts);
            $responseHeaders .= $responseParts[0];
            $responseBody .= $responseParts[1];

            // echo "\r\n\r\n";


       } else {
           // echo "$errno $errstr";
       }

       return $responseBody;
    }

    function get() {
        $hostport = $this->url_host . (!empty($this->url_port) ? (":".$this->url_port) : "");

        return @file_get_contents("http://" . $hostport . $this->url_path);



        $response = null;

        $fp = @pfsockopen($this->proxy_host, $this->proxy_port, $errno, $errstr, 2);
        $responseBody = "";

        if ($fp) {

            $headers = "GET http://".$hostport.$this->url_path." HTTP/1.1" . $this->nl .
                       "Host: ".$hostport."" . $this->nl;

            $headers .= $this->getExtraHeaders();

            $headers .= $this->nl;

            echo $headers;

            $response = "";
            $responseHeaders = "";
            while(!feof($fp)) {
                $resp = fgets($fp, 128);
                $response .= $resp;

                echo $resp;
            }

            $responseParts = explode("\r\n\r\n", $response);
            var_dump($responseParts);
            $responseHeaders .= $responseParts[0];
            $responseBody .= isset($responseParts[1]) ? $responseParts[1] : "";

            // echo "\r\n\r\n";


       } else {
           echo "Error: $errno $errstr";
       }

       return $responseBody;
    }

}

