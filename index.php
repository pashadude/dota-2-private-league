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

function countDotaStatsByMatches ($dota_id){
	$matches_mapper_web = new matches_mapper_web();
	$matches_mapper_web->set_account_id($dota_id);
	$matches_short_info = $matches_mapper_web->load();
	if (empty( $matches_short_info ) ) {  
	echo "the user has not shared matches";
	} else {
		$results = array();
		$k = 1;
		$results['gold_per_min'] = 0;
		$results['xp_per_min'] = 0;
		foreach ($matches_short_info AS $key=>$match_short_info) {
		    $match_mapper = new match_mapper_web($key);
		    $match = $match_mapper->load();
		    $slots = $match->get_all_slots();
		    	foreach ($slots as $slot) {
		    		$steam_id = (player::convert_id($slot->get('account_id')));
		    		if ($steam_id == $dota_id) {
		    			$results['kills'] += $slot->get('kills');
		    			$results['deaths'] += $slot->get('deaths');
		    			$results['assists'] += $slot->get('assists');
		    			//echo $slot->get('gold_per_min')."-gold".$slot->get('xp_per_min')."-exp";
		    			$results['gold_per_min'] = ($results['gold_per_min']*($k-1) + $slot->get('gold_per_min'))/$k;
		    			$results['xp_per_min'] = ($results['xp_per_min']*($k-1) + $slot->get('xp_per_min'))/$k;
						if ($match->get('radiant_win')) {
							if ($slot->get('player_slot')<128) {
								$results['wins']+=1;
							} else {
								$results['losses']+=1;
							}
						} else {
							if ($slot->get('player_slot')<128) {
								$results['losses']+=1;
							} else {
								$results['wins']+=1;
							}
						}
		    		}
		    	}
		    $k++;
		}
		return $results;
	}
}

function checkMatchPlayers ($team, $match){
	
}


function getMatchTeamPlayers ($slots, $players, $teamname){
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
		$results['winners'] = getMatchTeamPlayers ($slots, $players, 'radiant');
		$results['losers'] = getMatchTeamPlayers ($slots, $players, 'dire');
	} else {
		$results['winners'] = getMatchTeamPlayers ($slots, $players, 'dire');
		$results['losers'] = getMatchTeamPlayers ($slots, $players, 'radiant');
	}
	
	return $results;
}


//convertor
// steamid2dotaid('pys_paul');




//match results
//print_r (getMatchResults(37626434));

//one alias
//echo dotasname(steamid2dotaid('pashadudue'));

//statistics by player
print_r (countDotaStatsByMatches(steamid2dotaid('pys_paul')));



//all_league_matchez

//matchez of certain league, certain date, certain users



?>