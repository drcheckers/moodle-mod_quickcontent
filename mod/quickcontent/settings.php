<?php  

require_once($CFG->dirroot.'/mod/quickcontent/lib.php');

$settings->add(new admin_setting_configtext('quickcontent_embedlyusername', 'Embed.ly username',
                   'Enter the username used to register with embed.ly', ''));
$settings->add(new admin_setting_configtext('quickcontent_embedlykey', 'Embed.ly Key',
                   'Enter the key given by embed.ly', ''));

?>
