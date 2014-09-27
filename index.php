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


function dotasname($dota_id) {

	$players_mapper_web = new players_mapper_web();
	$players_info = $players_mapper_web->add_id($dota_id)->load();
	foreach($players_info as $player_info) {
	    $name=$player_info->get('personaname');
	    //echo $team->get('rating');
	}
	return $name;
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

function getMatchPlayers ($slots, $players, $teamname){
	for ($i=0; $i < 5; $i++) { 
		$steam_id = player::convert_id($slots[$teamname][$i]->get('account_id'));		
		if($steam_id != player::ANONYMOUS) {
	        $name = $players[$steam_id]->get('personaname');
	    } else {
	    	$name = "anonymous player";
	    }
	    $team[$i] = $name;
	}
	return $team;
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
	$results = array ();

    if($match->get('radiant_win')){
		$results['winners'] = getMatchPlayers ($slots, $players, 'radiant');
		$results['losers'] = getMatchPlayers ($slots, $players, 'dire');
	} else {
		$results['winners'] = getMatchPlayers ($slots, $players, 'dire');
		$results['losers'] = getMatchPlayers ($slots, $players, 'radiant');
	}
	
	return $results;
}


//convertor
//$doter = steamid2dotaid('pashadudue');

//match results
//print_r (getMatchResults(37626434));

//one alias
//echo dotasname(steamid2dotaid('pashadudue'));





//getLastMatches ($doter);



?>