<?
require_once('incentibox.php');
$PROGRAM_ID = 1000;
$API_USER = 'test';
$API_PASSWORD = 'test';

$client = new Incentibox($API_USER, $API_PASSWORD);

// returns all the redeemed_rewards for this program 
$rewards_array = $client->get_redeemed_rewards($PROGRAM_ID, 25);


var_dump($rewards_array);

exit
?>
