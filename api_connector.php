<?php
namespace CandyWrappers;

class API_Connector {

    protected $curl, $curl_opts;

    public function __construct(protected string $url, ?object $authentication = null, bool $verbose = false) {
        $this->curl = curl_init();
        $this->curl_opts = array (
            CURLOPT_HTTPHEADER => ['Content-type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_VERBOSE => $verbose
        );
        if ($authentication) {
            match ($authentication->type) {
                'token' => $this->set_authentication_bearer($authentication),
                'oauth2' => $this->set_authentication_oauth2($authentication),
                default => $this->set_authentication_basic($authentication),
            };
        }
    }
    
    public function __destruct() {
        isset($this->curl) && curl_close($this->curl);
    }
    
    protected function exec():mixed {
        return json_decode(curl_exec($this->curl), false);
    }
    
    protected function reset_opts():void {
        curl_reset($this->curl);
        curl_setopt_array($this->curl, $this->curl_opts);
    }
    
    public function get_error():string {
        return curl_error($this->curl);
    }

    public function get(string $path = '', array $parameters = []):mixed {
        $this->reset_opts();
        $url = $this->url.$path;
        foreach ($parameters as $key=>$param) {
            $url .= sprintf(
                    '%s%s=%s',
                    strpos($url, '?') !== false ? '&' : '?', 
                    rawurlencode($key), 
                    rawurlencode($param)
                );
        }
        curl_setopt($this->curl, CURLOPT_URL, $url);
        return $this->exec();
    }
    
    public function post(string $path = "", mixed $parameters = [], string $verb = "POST"):mixed {
        $this->reset_opts();
        curl_setopt_array($this->curl, [
            CURLOPT_POST => true,
            CURLOPT_URL => $this->url.$path,
            CURLOPT_CUSTOMREQUEST => $verb
        ]);
        $parameters && curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($parameters));
        return $this->exec();
    }
    
    public function put(string $path = "", mixed $parameters = []):mixed {
        return $this->post($path, $parameters, "PUT");
    }
    
    public function patch(string $path = "", mixed $parameters = []):mixed {
        return $this->post($path, $parameters, "PATCH");
    }
    
    public function delete(string $path = "", mixed $parameters = []):mixed {
        return $this->post($path, $parameters, "DELETE");
    }
    
    private function set_authentication_basic($authentication) {
        $this->curl_opts[CURLOPT_USERPWD] = sprintf('%s:%s', $authentication->user, $authentication->pass);
    }
    
    private function set_authentication_bearer($authentication) {
        $this->curl_opts[CURLOPT_HTTPHEADER] ??= [];
        array_push(
            $this->curl_opts[CURLOPT_HTTPHEADER],
            "Authorization: Bearer $authentication->token"
        );
    }
    
    private function set_authentication_oauth2($authentication) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $authentication->token_endpoint,
            CURLOPT_USERPWD => sprintf('%s:%s', $authentication->client_id, $authentication->client_secret),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => "grant_type=client_credentials"
        ]);
        $response = json_decode(curl_exec($ch));
        array_push(
            $this->curl_opts[CURLOPT_HTTPHEADER],
            "Authorization: Bearer $response->access_token"
        );
    }
    
}