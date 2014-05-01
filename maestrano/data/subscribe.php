<?php

//-----------------------------------------------
// Define root folder
//-----------------------------------------------
if (!defined('MAESTRANO_ROOT')) {
  define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../'));
}

require_once(MAESTRANO_ROOT . '/app/init/soa.php');

$maestrano = MaestranoService::getInstance();

if ($maestrano->isSoaEnabled() and $maestrano->getSoaUrl()) {
    $log = new MnoSoaBaseLogger();

    $notification = json_decode(file_get_contents('php://input'), false);
    $notification_entity = strtoupper(trim($notification->entity));
    
    $log->debug("Notification = ". json_encode($notification));
    
    switch ($notification_entity) {
            case "PERSONS":
                if (class_exists('MnoSoaPerson')) {
                    $mno_person = new MnoSoaPerson($opts['db_connection'], new MnoSoaBaseLogger());		
                    $mno_person->receiveNotification($notification);
                }
		break;
    }
}

?>