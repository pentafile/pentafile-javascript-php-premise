<?php

/**
 * Ejemplo de Integración de Pentafile SDK for PHP
 * www.pentafile.com
 */
require './Pentafile.php';

/**
 * Instancia de la variable PENTAFILE
 */
$PentafileAPI = new Pentafile("appkey");

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
        //$options = Array("random" => TRUE, "folder" => "mydocs");
        // $API->uploadFile($filename, $content_file,$options);
        $ObjectFile = $API->uploadFile($filename, $content_file);
        echo "key : " . $ObjectFile->getKey() . "<br>";
        echo "id : " . $ObjectFile->getId() . "<br>";
        echo "size : " . $ObjectFile->getSize() . "<br>";
        echo "type : " . $ObjectFile->getType() . "<br>";
        echo "url : " . $ObjectFile->getUrl() . "<br>";
    } catch (Exception $ex) {
        echo $ex;
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
        echo "key : " . $ObjectFile->getKey() . "<br>";
        echo "id : " . $ObjectFile->getId() . "<br>";
        echo "size : " . $ObjectFile->getSize() . "<br>";
        echo "type : " . $ObjectFile->getType() . "<br>";
        echo "url : " . $ObjectFile->getUrl() . "<br>";
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
        echo 'DELETE OK';
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
