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
	    $name = $player_info->get('personaname');
	}
	return $name;

}

function getAchievementTourneyResults ($data){
	$results = array();
	foreach ($data['ids'] as $dota_id) {
		$matches_mapper_web = new matches_mapper_web();
		$matches_mapper_web->set_account_id($dota_id);
		$matches_short_info = $matches_mapper_web->load();
		if (!empty( $matches_short_info ) ) { 			
			$k = 1;
			$results[$dota_id]['xp_per_min'] = 0;
			foreach ($matches_short_info AS $key=>$match_short_info) {
			    $match_mapper = new match_mapper_web($key);
			    $match = $match_mapper->load();
			    $starttime = ;
	            if ($starttime <= $data['starttime']) {
				    $slots = $match->get_all_slots();
				    	foreach ($slots as $slot) {
				    		$steam_id = (player::convert_id($slot->get('account_id')));
				    		if ($steam_id == $dota_id) {
				    			$results['xp_per_min'] = ($results[$dota_id]['xp_per_min']*($k-1) + $slot->get('xp_per_min'))/$k;
								if ($match->get('radiant_win')) {
									if ($slot->get('player_slot')<128) {
										$results[$dota_id]['wins']+=1;
									} else {
										$results[$dota_id]['losses']+=1;
									}
								} else {
									if ($slot->get('player_slot')<128) {
										$results[$dota_id]['losses']+=1;
									} else {
										$results[$dota_id]['wins']+=1;
									}
								}
				    		}
				    	}
					$k++;
	            }
		    }
		} 
	}
	return $results;
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



function getMatchInfo ($match_id){
	$results = array();
	if (isset($_GET['match_id'])) {
	    $match_id = intval($_GET['match_id']);
	}
	$match_mapper_web = new match_mapper_web($match_id);
	$match = $match_mapper_web->load();
	if (is_null($match)) {
	    die('<p>Match does not exists.</p>');
	}

	//print_r ($match);

	$results['starttime'] = $match->get('start_time');
	for ($i=0; $i < 5; $i++) { 
		$slot = $match->get_slot($i);
		$id = player::convert_id($slot->get('account_id'));
		//print_r ($id);
		
		if($id != player::ANONYMOUS) {
			$results['player'][$i]['name'] = dotasname($id);
		} else {
			$results['player'][$i]['name'] = 'anonymous player';
		}

		$j = $i + 128;
		$k = $i + 5;

		$slot = $match->get_slot($j);
		$id = player::convert_id($slot->get('account_id'));
		//print_r ($id);
		
		if($id != player::ANONYMOUS) {
			//echo $id." ";
			$results['player'][$k]['name'] = dotasname($id);
		} else {
			$results['player'][$k]['name'] = 'anonymous player';
		}
	}
	return $results;	
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

function getLeaguesId($name) {
	$leagues_mapper_web = new leagues_mapper_web();
	$leagues = $leagues_mapper_web->load();
	foreach($leagues as $league) {
	    if ( $league->get('name') == $name) {
	    	return $league->get('leagueid');
	    }
	}
}

function getLeagueMatchez($id){
	$league_mapper = new league_mapper($id);
	$games = $league_mapper->load();
	$matches_mapper_web = new matches_mapper_web();
	$matches_mapper_web->set_league_id($id);
	//$matches_short_info = $matches_mapper_web->load();
	//return ($matches_short_info);
	
	//return $games;
	$matches_short_info = $matches_mapper_web->load();
    $matches = array();

	$last_match_id = null;
	$all_are_loaded = false;
	$match_ids = array();
	
	while (!$all_are_loaded) {
		if (!is_null($last_match_id)) {
        	$matches_mapper_web->set_start_at_match_id($last_match_id - 1);
    	}

    	$matches_short_info = $matches_mapper_web->load();
    	$matches = array();
    	

    	if (!count($matches_short_info))  {
	        $all_are_loaded = true;
	    }

        foreach ($matches_short_info as $key=>$match_short_info) {
        	$match_mapper = new match_mapper_web($key);
            $match = $match_mapper->load();
            $last_match_id = $match->get('match_id'); 
            //echo $last_match_id;    
        	if (!in_array($last_match_id, $match_ids)) {
        		array_push($match_ids, $last_match_id);	
        	} else {
        		$all_are_loaded = true;
        	}
        }
	}

	return ($match_ids);

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
//echo dotasname(steamid2dotaid('Power_Never'));




//match results
//print_r (getMatchResults(37626434));

//dot alias
//echo dotasname(steamid2dotaid('pashadudue'));
//dotasname(76561198067910001);

//statistics by player
//print_r (countDotaStatsByMatches(steamid2dotaid('Power_Never')));


//print_r (countDotaStatsByMatches(steamid2dotaid(76561198073529900)));
//echo dotasname(steamid2dotaid(76561198073529900));

//match data neededto check if it is the match we are looking for
//print_r(getMatchInfo(37626434));


//league id by name
//echo getLeaguesId("ProveYourSkillz Tier 1: South America");

//all_league matchez which ever took place
//print_r (getLeagueMatchez(1791));


//getAllLeagueMatches(1791);


//matchez of certain league, certain date, certain users






?>