<?php

if (!defined('MODE') || MODE != 'INDEX') {
    die('Direct access denied!');
}

/**
 * Stores and gets the UID of the last backed up record.
 */
class UidStorage
{
    protected $storageName = '';
    protected $fullStoragePath = '';

    /**
     * @param string $storageName
     */
    public function __construct($storageName)
    {
        if (!trim($storageName)) {
            throw new Exception('You have to define a storageName!');
        }

        if (!defined('CWD')) {
            throw new Exception('You have to define a constant CWD to work in!');
        }

        $this->storageName = $storageName;
        $this->fullStoragePath = CWD . $this->storageName . '_storage.txt';

        if (!is_file($this->fullStoragePath)) {
            file_put_contents($this->fullStoragePath, '0');
        }
    }

    /**
     * Sets the uid.
     *
     * @param int $value
     */
    public function set($value)
    {
        file_put_contents($this->fullStoragePath, (int) $value);
    }

    /**
     * Gets the uid.
     *
     * @return int
     */
    public function get()
    {
        return (int) file_get_contents($this->fullStoragePath);
    }
}
