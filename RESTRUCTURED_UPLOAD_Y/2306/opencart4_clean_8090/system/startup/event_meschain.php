<?php
// system/startup/event_meschain.php
// Register the event to add MesChain to the admin menu
$event->register('admin/view/common/column_left/before', new \Opencart\System\Engine\Action('extension/meschain|menu'));
