<?php

if (!defined('MODE') || MODE != 'INDEX') {
    die('Direct access denied!');
}

/*
 * Make a copy of this file to config.php and fill in the values
 */
return [
    'DB' => [
        'username' => 'typo3',
        'password' => 'secret',
        'host' => '127.0.0.1',
        'database' => 'db_of_typo3_installation',
        'table' => 'tx_ext_domain_model_contribution',
    ],
    'ZIP' => [
        'DateString' => date('Ymd_His'),
        'password' => 'secret',
    ],
    'EMAIL' => [
        'to' => 'Name <name@domain.tld>',
        'from' => 'Domain.tld <mailer@domain.tld>',
        'returnPath' => 'Name <error@domain.tld>',
        'replyTo' => 'Name <error@domain.tld>',
        'subject' => 'Backup ',
    ],
];
