<?php

class StreamerAuth {

    private $ip;
    private $login;
    private $password;
    private $COOKIES;
    private $idStreamer;

    public function __construct($streamer) {
        $this->ip = $streamer['ip_addr'];
        $this->login = $streamer['auth_login'];
        $this->password = $streamer['auth_pw'];
        $this->idStreamer = $streamer['id'];

        $this->Login();
    }

    public function GetID() {
        return $this->idStreamer;
    }

    public function GetIP() {
        return $this->ip;
    }

    private function initAuth() {
        $ch = curl_init('http://' . $this->ip);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $result = curl_exec($ch);
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
        $cookies = array();
        foreach ($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        $this->COOKIES['MANAGER_INTERFACE'] = $cookie['MANAGER_INTERFACE'];
    }

    public function GetCookieString() {
        $str = '';
        foreach ($this->COOKIES as $key => $value) {
            $str .= $key . '=' . $value . ';';
        }

        return $str;
    }

    public function SendPost($url, array $request = [], $curlopt_header = 0) {
        $myCurl = curl_init();

        curl_setopt($myCurl, CURLOPT_URL, 'http://' . $this->ip . $url);
        curl_setopt($myCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($myCurl, CURLOPT_HEADER, $curlopt_header);
        curl_setopt($myCurl, CURLOPT_POST, true);
        curl_setopt($myCurl, CURLOPT_COOKIE, $this->GetCookieString());
        curl_setopt($myCurl, CURLOPT_POSTFIELDS, http_build_query($request));

        $response = json_decode(curl_exec($myCurl), true);
        curl_close($myCurl);

        return $response;
    }

    public function SendPut($url, array $request = [], $curlopt_header = 0) {

        $myCurl = curl_init();

        curl_setopt($myCurl, CURLOPT_URL, 'http://' . $this->ip . $url);
        curl_setopt($myCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($myCurl, CURLOPT_HEADER, $curlopt_header);
        curl_setopt($myCurl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($myCurl, CURLOPT_COOKIE, $this->GetCookieString());
        curl_setopt($myCurl, CURLOPT_POSTFIELDS, http_build_query($request));
        

        $response = json_decode(curl_exec($myCurl), true);
        curl_close($myCurl);

        return $response;
    }

    public function SendGet($url, $curlopt_header = 0) {
        $myCurl = curl_init();

        curl_setopt($myCurl, CURLOPT_URL, 'http://' . $this->ip . $url);
        curl_setopt($myCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($myCurl, CURLOPT_HEADER, $curlopt_header);
        curl_setopt($myCurl, CURLOPT_COOKIE, $this->GetCookieString());

        $response = json_decode(curl_exec($myCurl), true);

        curl_close($myCurl);

        return $response;
    }

    /**
     * 
     * @return type Возвращает строку с куками
     */
    private function Login() {

        $this->InitAuth();

        $myCurl = curl_init();

        curl_setopt($myCurl, CURLOPT_URL, 'http://' . $this->ip);
        curl_setopt($myCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($myCurl, CURLOPT_HEADER, 1);
        curl_setopt($myCurl, CURLOPT_POST, true);
        curl_setopt($myCurl, CURLOPT_COOKIE, $this->GetCookies('MANAGER_INTERFACE'));
        curl_setopt($myCurl, CURLOPT_POSTFIELDS, http_build_query(['login' => $this->login, 'password' => $this->password]));

        $response = curl_exec($myCurl);

        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
        $cookies = array();
        foreach ($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        curl_close($myCurl);
        //$this->COOKIES = //MANAGER_INTERFACE=; staffID=; staffName=; staffRole=; NBS_SESSION=

        $this->COOKIES = array_merge($this->COOKIES, $cookies);
        return $this->COOKIES;
    }

    private function GetCookies($param_name) {
        $cookie = '';
        if (isset($this->COOKIES[$param_name])) {
            $cookie = $param_name . '=' . $this->COOKIES[$param_name];
        }

        return $cookie;
    }

}
