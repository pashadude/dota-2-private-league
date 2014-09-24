<?php

require_once ('config.php');
require_once ('vendor/koraktor/steam-condenser/lib/steam-condenser.php');

function steamid2dotaid($playname){
	try
	{
	    $id = SteamId::create($playname);
	} 
	
	catch (SteamCondenserException $s)
	{
	    echo "steam name does not exist!";
	}

	$id_dota = $id->getSteamId64();
	return $id_dota;
}


function dotastats($dota_id) {

	$players_mapper_web = new players_mapper_web();
	$players_info = $players_mapper_web->add_id($dota_id)->load();
	foreach($players_info as $player_info) {
	    $player_info->get('personaname');
	    //echo $team->get('rating');
	}
	return $players_info;
}

function getLastMatches ($dota_id){
	$matches_mapper_web = new matches_mapper_web();
	$matches_mapper_web->set_account_id($dota_id);
	$matches_short_info = $matches_mapper_web->load();
	foreach ($matches_short_info AS $key=>$match_short_info) {
	    $match_mapper = new match_mapper_web($key);
	    $match = $match_mapper->load();
	    $mm = new match_mapper_db();
	    $mm->save($match);
	    //print_r($mm);
	}
}

function getMatchResults ($match_id){
	if (isset($_GET['match_id'])) {
	    $match_id = intval($_GET['match_id']);
	}
	$match_mapper_web = new match_mapper_web($match_id);
	$match = $match_mapper_web->load();
	if (is_null($match)) {
	    die('<p>Match does not exists.</p>');
	}
	$players_mapper_web = new players_mapper_web();
	foreach($match->get_all_slots() as $slot) {
	    if ($slot->get('account_id') != player::ANONYMOUS) {
	        $players_mapper_web->add_id(player::convert_id($slot->get('account_id')));
	    }
	}
	
	$players = $players_mapper_web->load();
	$slots = $match->get_all_slots_divided();
	$results = array();
	print_r($slots);

	/*foreach ($slots as $k=>$team) {
		print_r($slots['dire']);
		foreach ($variable as $key => $value) {
			# code...
		}
	}*/

	/*if($match->get('radiant_win')){
		foreach ($variable as $key => $value) {
			# code...
		}
		$steam_id = player::convert_id($slot->get('account_id'));
	} else {

	}
	foreach ($variable as $key => $value) {
		# code...
	}*/
}



$doter = steamid2dotaid('pashadudue');
getMatchResults(37626434);
//echo $doter;
//print_r (dotastats($doter));

//getLastMatches ($doter);



?>