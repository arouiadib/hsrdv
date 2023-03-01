<?php

require_once(dirname(__FILE__) . '/../../config/config.inc.php');
$_SERVER['REQUEST_METHOD'] = "POST";
require_once(dirname(__FILE__) . '/../../init.php');
require_once(dirname(__FILE__) . '/hsrdv.php');

$oModule = new Hsrdv();

$oModule->cronSendMailsEnqueteSatisfaction();

