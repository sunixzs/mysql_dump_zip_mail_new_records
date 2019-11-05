<?php

if (!defined('MODE') || MODE != 'INDEX') {
    die('Direct access denied!');
}

class Zip
{
    /**
     * @param string $filepath
     * @param string $password
     *
     * @return string Path to zip-file
     */
    public static function createFromFileWithPassword($filepath, $password)
    {
        Logger::log("create zip-archive from {$filepath} ...");

        $zipDirectory = dirname($filepath);
        $filename = basename($filepath);
        $zipFilename = $filename . '.zip';

        // create the zip
        $maskedPassword = escapeshellarg($password);
        chdir($zipDirectory);
        exec("zip -P {$maskedPassword} {$zipFilename} {$filename}", $ret, $retval);

        Logger::log($ret);

        if (0 != $retval) {
            die('ERROR: SOMETHING WENT WRONG WHILE ZIPPING!');
        }

        return $zipDirectory . '/' . $zipFilename;
    }
}
