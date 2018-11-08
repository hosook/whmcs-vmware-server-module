<?php

class Client
{
    public $url;

    public $token;

    public $machine;

    public function __construct($url, $username, $password)
    {
        $this->url = $url.'/rest/';

        $this->token($username, $password);
    }

    public function token($username, $password)
    {
        $res = $this->send("com/vmware/cis/session", [
                    'username' => $username,
                    'password' => $password
                ], [], false, 'POST');

        $this->token = $res['value'];
    }

    public function find($ip)
    {
        $host = $this->get("?filter.names={$ip}");

        $machine = false;

        foreach ($host['value'] as $host) {
            if ($host['name'] == $ip) {
                $machine = $host;
            }
        }

        $this->machine = $machine;

        return $this;
    }


    public function reset()
    {
        $this->power('reset');
    }

    public function stop()
    {
        $this->power('suspend');
    }

    public function start()
    {
        $this->power('start');
    }

    public function power($power)
    {
        $this->post("/{$this->machine['vm']}/power/{$power}");
    }

    public function get($url)
    {
        return $this->send("vcenter/vm{$url}", false, false, [
            'vmware-api-session-id: ' . $this->token
        ], 'GET');
    }

    public function post($url)
    {
        return $this->send("vcenter/vm{$url}", false, [], [
            'vmware-api-session-id: ' . $this->token
        ], 'POST');
    }

    public function send($url, $auth = false, $params, $headers, $method = 'GET')
    {
        $url = $this->url . $url;


        $querystring = null;

        if (is_array($params)) {
            $querystring = '?' . http_build_query($params);
        }
        if ($auth) {
            $headers[] = 'Authorization: Basic ' . base64_encode($auth['username'] . ':' . $auth['password']);
        }

        $url = $url . ('GET' === $method ? $querystring : null);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        switch ($method) {
           case 'PUT':
               curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
               curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
               break;
           case 'POST':
               curl_setopt($ch, CURLOPT_POST, count($params));
               curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
               break;
           case 'DELETE':
               curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
               break;
        }
        $jsonData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response = json_decode($jsonData, true);
        curl_close($ch);

        return $response;
    }
}
