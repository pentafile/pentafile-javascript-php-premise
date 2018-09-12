# pentafile-php-sdk
Pentafile SDK for PHP

### SDK de Pentafile Premise
```php
<?php

/**
 * Ejemplo de Integración de Pentafile SDK for PHP
 * www.pentafile.com
 */
require './Pentafile.php';

/**
 * Instancia de la variable PENTAFILE
 */
$PentafileAPI = new Pentafile("http://IP-SERVER:8080/pentafile", "appkey");

/**
 * Carga de archivo de disco
 */
$file_path = dirname(__FILE__) . '/bitnami.css';
/**
 * Obtenemos el binario del archivo
 */
$file_content = file_get_contents($file_path);

uploadFile($PentafileAPI, basename($file_path), $file_content);

/**
 * 
 * @param type $API
 * @param type $filename
 * @param type $content_file
 */
function uploadFile($API, $filename, $content_file) {
    try {
        $ObjectFile = $API->uploadFile($filename, $content_file);
        echo $ObjectFile->getKey() . "<br>";
        echo $ObjectFile->getFilename() . "<br>";
        echo $ObjectFile->getSize() . "<br>";
        echo $ObjectFile->getType() . "<br>";
        echo $ObjectFile->getUrl() . "<br>";
    } catch (Exception $ex) {
        echo "Error: " . $ex;
    }
}

/**
 * Obtener información del archivo
 * @param type $API
 * @param type $key_file
 */
function infoFile($API, $key_file) {
    try {
        $ObjectFile = $API->infoFile($key_file);
        echo $ObjectFile->getKey() . "<br>";
        echo $ObjectFile->getFilename() . "<br>";
        echo $ObjectFile->getSize() . "<br>";
        echo $ObjectFile->getType() . "<br>";
        echo $ObjectFile->getUrl() . "<br>";
    } catch (Exception $ex) {
        echo "Error: " . $ex;
    }
}

/**
 * Eliminar el archivo
 * @param type $API
 * @param type $key_file
 */
function deleteFile($API, $key_file) {
    try {
        $API->deleteFile($key_file);
        // No return content
    } catch (Exception $ex) {
        echo "Error: " . $ex;
    }
}

/**
 * Descargar el archivo
 * @param type $API
 * @param type $key_file
 */
function downloadFile($API, $key_file) {
    try {
        $InputStream = $API->downloadFile($key_file);
        /**
         * Guardamos en contenido binario del archivo
         */
        $my_file = dirname(__FILE__) . '/' . $key_file . '.css';
        $handle = fopen($my_file, 'w') or die('Cannot open file:  ' . $my_file);
        fwrite($handle, $InputStream);
    } catch (Exception $ex) {
        echo "Error: " . $ex;
    }
}
```
