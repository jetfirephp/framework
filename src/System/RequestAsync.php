<?php

namespace JetFire\Framework\System;


/**
 * Class RequestAsync
 * @package JetFire\Framework\System
 */
class RequestAsync
{
    /**
     * @param $address
     * @return int
     */
    public function get($address)
    {
        $url = parse_url($address);
        $errno = '';
        $errstr = '';

        $fp = fsocketopen($url['host'], 80, $errno, $errstr, 30);

        $out = "GET " . $url['path'] . " HTTP/1.1\r\n";
        $out .= "Host: " . $url['host'] . "\r\n";
        $out .= "Content-Type: text/json\r\n";
        $out .= "Content-Length: 0\r\n";
        $out .= "Connection: Close\r\n";

        fwrite($fp, $out);
        fclose($fp);

        return 0;
    }

    /**
     * @param $address
     * @param $data
     * @return int
     */
    public function post($address, $data)
    {
        $post_string = json_encode($data);
        $url = parse_url($address);
        $errno = '';
        $errstr = '';

        $fp = fsocketopen($url['host'], 80, $errno, $errstr, 30);

        $out = "POST " . $url['path'] . " HTTP/1.1\r\n";
        $out .= "Host: " . $url['host'] . "\r\n";
        $out .= "Content-Type: text/json\r\n";
        $out .= "Content-Length: " . strlen($post_string) . "\r\n";
        $out .= "Connection: Close\r\n";
        $out .= $post_string;

        fwrite($fp, $out);
        fclose($fp);

        return 0;
    }
}