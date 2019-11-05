<?php
/**
 * Script to get the latest entries out of a database with a columne named 'uid'.
 */
header('Content-type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>back it up and send per mail</title>
</head>

<body><?php

define('MODE', 'INDEX');
define('CWD', dirname(__FILE__) . '/');

require CWD . 'Classes/Logger.php';
require CWD . 'Classes/Database.php';
require CWD . 'Classes/UidStorage.php';
require CWD . 'Classes/Mysqldump.php';
require CWD . 'Classes/Zip.php';
require CWD . 'Classes/Mailer.php';

// load config
if (!is_file(CWD . 'config.php')) {
    Logger::log('ERROR: Could not find config.php!');
    exit;
}

$config = require CWD . 'config.php';
if (!is_array($config)) {
    Logger::log('ERROR: config.php has to return an array!');
    exit;
}

// init database and make global
try {
    $database = new Database($config['DB']['host'], $config['DB']['username'], $config['DB']['password'], $config['DB']['database']);
    $GLOBALS['DB'] = $database->get();
} catch (Exception $e) {
    Logger::log('ERROR WITH DATABASE: ' . $e->getMessage());
    exit;
}

// init storage
$uidStorage = new UidStorage($config['DB']['table']);

// generate a dump of new data
Logger::log('CREATE MYSQLDUMP');
$mysqldump = new Mysqldump($config['ZIP']['DateString'], $config['DB']['host'], $config['DB']['database'], $config['DB']['table']);
$dump = $mysqldump->getDumpData($uidStorage->get());
$database->close();

if (!$mysqldump->isThereADump()) {
    // no new data so nothing is to do
    die();
}

$startUid = $mysqldump->getStartUid();
$endUid = $mysqldump->getEndUid();

// store dump as file
$workingDirectory = CWD . 'tmp/';
$dumpFilename = 'mysqldump_' . $config['DB']['table'] . '_' . $config['ZIP']['DateString'] . '.sql';
$dumpFilepath = $workingDirectory . $dumpFilename;
file_put_contents($dumpFilepath, $dump);

// create zip
Logger::log('CREATE ZIP OF MYSQLDUMP');
$zipFilepath = Zip::createFromFileWithPassword($dumpFilepath, $config['ZIP']['password']);

// send mail
Logger::log('SEND MAIL');
$mailer = new Mailer();
$mailer->send($config['EMAIL']['to'], $config['EMAIL']['subject'] . '(' . $startUid . '-' . $endUid . ')', $config['EMAIL']['from'], $config['EMAIL']['returnPath'], $config['EMAIL']['replyTo'], $zipFilepath);

Logger::log('CLEAN UP');

sleep(10);
Logger::log('... remove zip-file');
unlink($zipFilepath);

Logger::log('... remove sql-file');
unlink($dumpFilepath);

Logger::log('... save UID: ' . $endUid);
$uidStorage->set((int) $endUid);

Logger::log('THIS IS THE END!');

?>
</body>

</html>