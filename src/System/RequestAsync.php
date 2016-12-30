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
     * @param array $data
     */
    public function get($address, $data = [])
    {
        $post_params = [];
        $errno = '';
        $errstr = '';
        foreach ($data as $key => &$val) {
            if (is_array($val)) $val = implode(',', $val);
            $post_params[] = $key.'='.urlencode($val);
        }
        $post_string = implode('&', $post_params);

        $url = parse_url($address);

        $fp = fsockopen($url['host'], 80, $errno, $errstr, 30);

        $url['path'] .= '?' . $post_string;

        $out = "POST " . $url['path'] . " HTTP/1.1\r\n";
        $out .= "Host: " . $url['host'] . "\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "Content-Length: " . strlen($post_string) . "\r\n";
        $out .= "Connection: Close\r\n\r\n";

        fwrite($fp, $out);
        fclose($fp);
    }

    /**
     * @param $address
     * @param $data
     */
    public function post($address, $data = [])
    {
        $post_params = [];
        $errno = '';
        $errstr = '';
        foreach ($data as $key => &$val) {
            if (is_array($val)) $val = implode(',', $val);
            $post_params[] = $key.'='.urlencode($val);
        }
        $post_string = implode('&', $post_params);

        $url = parse_url($address);

        $fp = fsockopen($url['host'], 80, $errno, $errstr, 30);

        $out = "POST " . $url['path'] . " HTTP/1.1\r\n";
        $out .= "Host: " . $url['host'] . "\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "Content-Length: " . strlen($post_string) . "\r\n";
        $out .= "Connection: Close\r\n\r\n";
        if (isset($post_string)) $out .= $post_string;

        fwrite($fp, $out);
        fclose($fp);
    }
}