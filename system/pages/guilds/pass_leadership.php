<?php
/**
 * Pass leadership
 *
 * @package   MyAAC
 * @author    Gesior <jerzyskalski@wp.pl>
 * @author    Slawkens <slawkens@gmail.com>
 * @copyright 2017 MyAAC
 * @version   0.6.1
 * @link      http://my-aac.org
 */
defined('MYAAC') or die('Direct access not allowed!');

$guild_name = isset($_REQUEST['guild']) ? $_REQUEST['guild'] : NULL;
$pass_to = isset($_REQUEST['player']) ? stripslashes($_REQUEST['player']) : NULL;
if(!Validator::guildName($guild_name)) {
	$guild_errors[] = Validator::getLastError();
}

if(empty($guild_errors)) {
	$guild = $ots->createObject('Guild');
	$guild->find($guild_name);
	if(!$guild->isLoaded()) {
		$guild_errors[] = "Guild with name <b>" . $guild_name . "</b> doesn't exist.";
	}
}
if(empty($guild_errors)) {
	if(isset($_POST['todo']) && $_POST['todo'] == 'save') {
		if(!Validator::characterName($pass_to)) {
			$guild_errors2[] = 'Invalid player name format.';
		}
		
		if(empty($guild_errors2)) {
			$to_player = new OTS_Player();
			$to_player->find($pass_to);
			if(!$to_player->isLoaded()) {
				$guild_errors2[] = 'Player with name <b>'.$pass_to.'</b> doesn\'t exist.';
			}
			
			if(empty($guild_errors2)) {
				$to_player_rank = $to_player->getRank();
				if($to_player_rank->isLoaded()) {
					$to_player_guild = $to_player_rank->getGuild();
					if($to_player_guild->getId() != $guild->getId()) {
						$guild_errors2[] = 'Player with name <b>'.$to_player->getName().'</b> isn\'t from your guild.';
					}
				}
				else {
					$guild_errors2[] = 'Player with name <b>'.$to_player->getName().'</b> isn\'t from your guild.';
				}
			}
		}
	}
}
if(empty($guild_errors) && empty($guild_errors2)) {
	if($logged) {
		$guild_leader_char = $guild->getOwner();
		$guild_leader = false;
		$account_players = $account_logged->getPlayers();
		foreach($account_players as $player) {
			if($guild_leader_char->getId() == $player->getId()) {
				$guild_vice = true;
				$guild_leader = true;
				$level_in_guild = 3;
			}
		}
		
		$saved = false;
		if($guild_leader) {
			if(isset($_POST['todo']) && $_POST['todo'] == 'save') {
				$query = $db->query('SELECT `id` FROM `guild_ranks` WHERE `guild_id` = ' . $guild->getId() . ' ORDER BY `level` ASC LIMIT 1')->fetch();
				if($query) {
					$guild_leader_char->setRankId($query['id'], $guild->getId());
				}
				
				$query = $db->query('SELECT `id` FROM `guild_ranks` WHERE `guild_id` = ' . $guild->getId() . ' ORDER BY `level` DESC LIMIT 1')->fetch();
				if($query) {
					$to_player->setRankId($query['id'], $guild->getId());
				}
				
				$guild->setOwner($to_player);
				$guild->save();
				$saved = true;
			}
			if($saved) {
				echo $twig->render('success.html.twig', array(
					'title' => 'Leadership passed',
					'description' => '<b>'.$to_player->getName().'</b> is now a Leader of <b>'.$guild_name.'</b>.',
					'custom_buttons' => '<center><form action="?subtopic=guilds&guild='.$guild->getName().'&action=show" METHOD=post><div class="BigButton" style="background-image:url('.$template_path.'/images/buttons/sbutton.gif)" ><div onMouseOver="MouseOverBigButton(this);" onMouseOut="MouseOutBigButton(this);" ><div class="BigButtonOver" style="background-image:url('.$template_path.'/images/buttons/sbutton_over.gif);" ></div><input class="ButtonText" type="image" name="Back" alt="Back" src="'.$template_path.'/images/buttons/_sbutton_back.gif" ></div></div></form></center>'
				));
			}
			else {
				echo $twig->render('guilds.pass_leadership.html.twig', array(
					'guild' => $guild
				));
			}
		}
		else {
			$guild_errors[] = 'You are not a leader of guild!';
		}
	}
	else {
		$guild_errors[] = "You are not logged. You can't manage guild.";
	}
}
if(empty($guild_errors) && !empty($guild_errors2)) {
	echo $twig->render('error_box.html.twig', array('errors' => $guild_errors2));
	
	echo '<br/><center><form action="?subtopic=guilds&guild='.$guild->getName().'&action=pass_leadership" METHOD=post><div class="BigButton" style="background-image:url('.$template_path.'/images/buttons/sbutton.gif)" ><div onMouseOver="MouseOverBigButton(this);" onMouseOut="MouseOutBigButton(this);" ><div class="BigButtonOver" style="background-image:url('.$template_path.'/images/buttons/sbutton_over.gif);" ></div><input class="ButtonText" type="image" name="Back" alt="Back" src="'.$template_path.'/images/buttons/_sbutton_back.gif" ></div></div></form></center>';
}
if(!empty($guild_errors)) {
	if(!empty($guild_errors2)) {
		$guild_errors = array_merge($guild_errors, $guild_errors2);
	}
	echo $twig->render('error_box.html.twig', array('errors' => $guild_errors));
	
	echo '<br/><center><form action="?subtopic=guilds" METHOD=post><div class="BigButton" style="background-image:url('.$template_path.'/images/buttons/sbutton.gif)" ><div onMouseOver="MouseOverBigButton(this);" onMouseOut="MouseOutBigButton(this);" ><div class="BigButtonOver" style="background-image:url('.$template_path.'/images/buttons/sbutton_over.gif);" ></div><input class="ButtonText" type="image" name="Back" alt="Back" src="'.$template_path.'/images/buttons/_sbutton_back.gif" ></div></div></form></center>';
}

?>