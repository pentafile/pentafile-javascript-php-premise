<?php

/**
 * Pentafile 1.0.0
 * SDK Pentafile for PHP
 * www.pentafile.com
 */
class Pentafile {

    private $host;
    private $appkey;
    private $api;

    public function __construct($appkey) {
        $this->host = "https://api.pentafilestore.com/storage/v1";
        $this->appkey = $appkey;
        $this->api = new RestClient(['base_url' => $this->host]);
    }

    /**
     * Método para cargar un archivo a Pentafile
     * @param type $filename - nombre del archivo,file.png,archivo.txt,etc
     * @param type $content - contenido del archivo
     * @options type $options - Configuracion
     * @return \ObjectFile
     * @throws PentafileException
     */
    public function uploadFile($filename, $content, $options = []) {
        try {
            $fields = array("random" => "false", "name" => basename($filename));
            if (empty($options)) {
                $fields = array("random" => "true", "name" => basename($filename));
            } else {
                $fields = array();
                foreach ($options as $key => $value) {
                    if ($key == "random") {
                        if ($value == TRUE) {
                            $fields[$key] = "true";
                        } else {
                            $fields[$key] = "false";
                        }
                    } else {
                        $fields[$key] = $value;
                    }
                }
                $fields['name'] = basename($filename);
            }
            $files = array($filename => $content);
            $boundary = uniqid();
            $post_form = $this->build_form_upload($boundary, $fields, $files);
            $result = $this->api->upload("/repositories/" . $this->appkey, $post_form);
            if ($result->info->http_code == 200) {
                return new ObjectFile($result->response);
            } else {
                $source = (array) json_decode($result->response);
                throw new PentafileException($source['message']);
            }
        } catch (Exception $ex) {
            throw new PentafileException($ex->getMessage());
        }
    }

    /**
     * Método para descargar un archivo desde Pentafile
     * @param type $key - key del archivo
     * @return binary - retorna el contenido binario del archivo
     * @throws PentafileException
     */
    public function downloadFile($key) {
        try {
            $result = $this->api->get("/repositories/" . $this->appkey . '/' . $key);
            if ($result->info->http_code == 200) {
                return $result->response;
            } else {
                $source = (array) json_decode($result->response);
                throw new PentafileException($source['message']);
            }
        } catch (Exception $ex) {
            throw new PentafileException($ex->getMessage());
        }
    }

    /**
     * Método para eliminar un archivos a Pentafile
     * @param type $key - key del archivo
     * @return type no content - No retorna contenido
     * @throws PentafileException
     */
    public function deleteFile($key) {
        try {
            $result = $this->api->delete("/repositories/" . $this->appkey . '/' . $key, array(), array('Content-Type' => 'application/json'));
            if ($result->info->http_code == 204) {
                return;
            } else {
                $source = (array) json_decode($result->response);
                throw new PentafileException($source['message']);
            }
        } catch (Exception $ex) {
            throw new PentafileException($ex->getMessage());
        }
    }

    /**
     * Método para obtener la información del archivo
     * @param type $key -  key del archivo
     * @return \ObjectFile - Objeto del archivo Pentafile
     * @throws PentafileException
     */
    public function infoFile($key) {
        try {
            $result = $this->api->get("/repositories/" . $this->appkey . '/' . $key . ':info', array(), array('Content-Type' => 'application/json'));
            if ($result->info->http_code == 200) {
                return new ObjectFile($result->response);
            } else {
                $source = (array) json_decode($result->response);
                throw new PentafileException($source['message']);
            }
        } catch (Exception $ex) {
            print_r($ex);
            throw new PentafileException($ex->getMessage());
        }
    }

    function build_form_upload($boundary, $fields, $files) {
        $data = '';
        $eol = "\r\n";
        $delimiter = '-------------' . $boundary;
        foreach ($fields as $name => $content) {
            $data .= "--" . $delimiter . $eol
                    . 'Content-Disposition: form-data; name="' . $name . "\"" . $eol . $eol
                    . $content . $eol;
        }
        foreach ($files as $name => $content) {
            $data .= "--" . $delimiter . $eol
                    . 'Content-Disposition: form-data; name="file"; filename="' . $name . '"' . $eol
                    . 'Content-Transfer-Encoding: binary' . $eol
            ;
            $data .= $eol;
            $data .= $content . $eol;
        }
        $data .= "--" . $delimiter . "--" . $eol;
        return $data;
    }

}

class PentafileException extends Exception {

    public function __construct($message, Exception $previous = null) {
        parent::__construct($message, $previous);
    }

}

/**
 * Class ObjectFile de Pentafile
 */
class ObjectFile {

    /**
     * Clave única del archivo, generado por el servidor
     */
    private $key;

    /**
     * name nombre del archivo, proporcionado por el cliente
     */
    private $id;

    /**
     * Date fecha de carga
     */
    private $created;

    /**
     * Size tamaño del archivo, determina el server
     */
    private $size;

    /**
     * MimeType del archivo, determina el server
     */
    private $type;

    /**
     * Url cdn file
     */
    private $url;

    public function __construct($data) {
        $source = (array) json_decode($data);
        $this->key = $source['key'];
        $this->id = $source['id'];
        $this->type = $source['type'];
        $this->size = $source['size'];
        $this->url = $source['url'];
    }

    public function getKey() {
        return $this->key;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getSize() {
        return $this->size;
    }

    public function getType() {
        return $this->type;
    }

    public function getUrl() {
        return $this->url;
    }

    public function setKey($key) {
        $this->key = $key;
    }

    public function setCreated($created) {
        $this->created = $created;
    }

    public function setSize($size) {
        $this->size = $size;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    function getId() {
        return $this->id;
    }

    function setId($id) {
        $this->id = $id;
    }

}

class RestClientException extends Exception {
    
}

class RestClient implements Iterator, ArrayAccess {

    public $options;
    public $handle;
    public $response;
    public $headers;
    public $info;
    public $error;
    public $response_status_lines;
    public $decoded_response;
    public $method;

    public function __construct($options = []) {
        $default_options = [
            'headers' => [],
            'parameters' => [],
            'curl_options' => [],
            'build_indexed_queries' => FALSE,
            'user_agent' => "PHP RestClient/0.1.7",
            'base_url' => NULL,
            'format' => NULL,
            'format_regex' => "/(\w+)\/(\w+)(;[.+])?/",
            'decoders' => [
                'json' => 'json_decode',
                'php' => 'unserialize'
            ],
            'username' => NULL,
            'password' => NULL
        ];
        $this->options = array_merge($default_options, $options);
        if (array_key_exists('decoders', $options))
            $this->options['decoders'] = array_merge(
                    $default_options['decoders'], $options['decoders']);
    }

    public function set_option($key, $value) {
        $this->options[$key] = $value;
    }

    public function register_decoder($format, $method) {
        $this->options['decoders'][$format] = $method;
    }

    public function rewind() {
        $this->decode_response();
        return reset($this->decoded_response);
    }

    public function current() {
        return current($this->decoded_response);
    }

    public function key() {
        return key($this->decoded_response);
    }

    public function next() {
        return next($this->decoded_response);
    }

    public function valid() {
        return is_array($this->decoded_response) && (key($this->decoded_response) !== NULL);
    }

    // ArrayAccess methods:
    public function offsetExists($key) {
        $this->decode_response();
        return is_array($this->decoded_response) ?
                isset($this->decoded_response[$key]) : isset($this->decoded_response->{$key});
    }

    public function offsetGet($key) {
        $this->decode_response();
        if (!$this->offsetExists($key))
            return NULL;
        return is_array($this->decoded_response) ?
                $this->decoded_response[$key] : $this->decoded_response->{$key};
    }

    public function offsetSet($key, $value) {
        throw new RestClientException("Decoded response data is immutable.");
    }

    public function offsetUnset($key) {
        throw new RestClientException("Decoded response data is immutable.");
    }

    public function get($url, $parameters = [], $headers = []) {
        return $this->execute($url, 'GET', $parameters, $headers);
    }

    public function post($url, $parameters = [], $headers = []) {
        return $this->execute($url, 'POST', $parameters, $headers);
    }

    public function put($url, $parameters = [], $headers = []) {
        return $this->execute($url, 'PUT', $parameters, $headers);
    }

    public function patch($url, $parameters = [], $headers = []) {
        return $this->execute($url, 'PATCH', $parameters, $headers);
    }

    public function delete($url, $parameters = [], $headers = []) {
        return $this->execute($url, 'DELETE', $parameters, $headers);
    }

    public function head($url, $parameters = [], $headers = []) {
        return $this->execute($url, 'HEAD', $parameters, $headers);
    }

    public function upload($url, $data) {
        $client = clone $this;
        $client->url = $url;
        $client->handle = curl_init();
        $headersx = array("Content-Type:multipart/form-data");
        $curlopt = [
            CURLOPT_HEADER => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_USERAGENT => $client->options['user_agent'],
            CURLOPT_HTTPHEADER => $headersx,
            CURLOPT_RETURNTRANSFER => TRUE
        ];
        $curlopt[CURLOPT_POST] = TRUE;
        $curlopt[CURLOPT_POSTFIELDS] = $data;
        $curlopt[CURLOPT_SSL_VERIFYPEER] = false;
        if ($client->options['base_url']) {
            $client->url = $client->options['base_url'] . $client->url;
        }
        $curlopt[CURLOPT_URL] = $client->url;
        if ($client->options['curl_options']) {
            foreach ($client->options['curl_options'] as $key => $value) {
                $curlopt[$key] = $value;
            }
        }
        curl_setopt_array($client->handle, $curlopt);
        $client->parse_response(curl_exec($client->handle));
        $client->info = (object) curl_getinfo($client->handle);
        $client->error = curl_error($client->handle);
        curl_close($client->handle);
        return $client;
    }

    public function execute($url, $method = 'GET', $parameters = [], $headers = []) {
        $client = clone $this;
        $client->url = $url;
        $client->handle = curl_init();
        $client->method = $method;
        $curlopt = [
            CURLOPT_HEADER => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_USERAGENT => $client->options['user_agent']
        ];
        if (count($client->options['headers']) || count($headers)) {
            $curlopt[CURLOPT_HTTPHEADER] = [];
            $headers = array_merge($client->options['headers'], $headers);
            foreach ($headers as $key => $values) {
                foreach (is_array($values) ? $values : [$values] as $value) {
                    $curlopt[CURLOPT_HTTPHEADER][] = sprintf("%s:%s", $key, $value);
                }
            }
        }
        if ($client->options['format'])
            $client->url .= '.' . $client->options['format'];
        if (is_array($parameters)) {
            $parameters = array_merge($client->options['parameters'], $parameters);
            $parameters_string = http_build_query($parameters);
            if (!$client->options['build_indexed_queries'])
                $parameters_string = preg_replace(
                        "/%5B[0-9]+%5D=/simU", "%5B%5D=", $parameters_string);
        } else
            $parameters_string = (string) $parameters;
        if (strtoupper($method) == 'POST') {
            $curlopt[CURLOPT_POST] = TRUE;
            $curlopt[CURLOPT_POSTFIELDS] = $parameters_string;
        } elseif (strtoupper($method) != 'GET') {
            $curlopt[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
            $curlopt[CURLOPT_POSTFIELDS] = $parameters_string;
        } elseif (strtoupper($method) != 'DELETE') {
            $curlopt[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
            $curlopt[CURLOPT_POSTFIELDS] = $parameters_string;
        } elseif ($parameters_string) {
            $client->url .= strpos($client->url, '?') ? '&' : '?';
            $client->url .= $parameters_string;
        }
        if ($client->options['base_url']) {
            if ($client->url[0] != '/' && substr($client->options['base_url'], -1) != '/')
                $client->url = '/' . $client->url;
            $client->url = $client->options['base_url'] . $client->url;
        }
        $curlopt[CURLOPT_URL] = $client->url;
        $curlopt[CURLOPT_SSL_VERIFYPEER] = false;
        if ($client->options['curl_options']) {
            // array_merge would reset our numeric keys.
            foreach ($client->options['curl_options'] as $key => $value) {
                $curlopt[$key] = $value;
            }
        }
        curl_setopt_array($client->handle, $curlopt);
        $client->parse_response(curl_exec($client->handle));
        $client->info = (object) curl_getinfo($client->handle);
        $client->error = curl_error($client->handle);
        curl_close($client->handle);
        return $client;
    }

    public function parse_response($response) {
        $headers = [];
        $this->response_status_lines = [];
        $line = strtok($response, "\n");
        do {
            if (strlen(trim($line)) == 0) {
                // Since we tokenize on \n, use the remaining \r to detect empty lines.
                if (count($headers) > 0)
                    break; // Must be the newline after headers, move on to response body
            }
            elseif (strpos($line, 'HTTP') === 0) {
                // One or more HTTP status lines
                $this->response_status_lines[] = trim($line);
            } else {
                // Has to be a header
                list($key, $value) = explode(':', $line, 2);
                $key = trim(strtolower(str_replace('-', '_', $key)));
                $value = trim($value);
                if (empty($headers[$key]))
                    $headers[$key] = $value;
                elseif (is_array($headers[$key]))
                    $headers[$key][] = $value;
                else
                    $headers[$key] = [$headers[$key], $value];
            }
        } while ($line = strtok("\n"));
        $this->headers = (object) $headers;
        $this->response = strtok("");
    }

    public function get_response_format() {
        if (!$this->response)
            throw new RestClientException(
            "A response must exist before it can be decoded.");
        // User-defined format. 
        if (!empty($this->options['format']))
            return $this->options['format'];
        // Extract format from response content-type header. 
        if (!empty($this->headers->content_type))
            if (preg_match($this->options['format_regex'], $this->headers->content_type, $matches))
                return $matches[2];
        throw new RestClientException(
        "Response format could not be determined.");
    }

    public function decode_response() {
        if (empty($this->decoded_response)) {
            $format = $this->get_response_format();
            if (!array_key_exists($format, $this->options['decoders']))
                throw new RestClientException("'${format}' is not a supported " .
                "format, register a decoder to handle this response.");
            $this->decoded_response = call_user_func(
                    $this->options['decoders'][$format], $this->response);
        }
        return $this->decoded_response;
    }

}
