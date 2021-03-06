<?php
/**
 * Accept invite
 *
 * @package   MyAAC
 * @author    Gesior <jerzyskalski@wp.pl>
 * @author    Slawkens <slawkens@gmail.com>
 * @copyright 2017 MyAAC
 * @version   0.6.6
 * @link      http://my-aac.org
 */
defined('MYAAC') or die('Direct access not allowed!');

//set rights in guild
$guild_name = isset($_REQUEST['guild']) ? $_REQUEST['guild'] : NULL;
$name = isset($_REQUEST['name']) ? stripslashes($_REQUEST['name']) : NULL;
if(!$logged) {
	$errors[] = 'You are not logged in. You can\'t accept invitations.';
}
if(!Validator::guildName($guild_name)) {
	$errors[] = Validator::getLastError();
}
if(empty($errors)) {
	$guild = $ots->createObject('Guild');
	$guild->find($guild_name);
	if(!$guild->isLoaded()) {
		$errors[] = 'Guild with name <b>'.$guild_name.'</b> doesn\'t exist.';
	}
}

if(isset($_REQUEST['todo']) && $_REQUEST['todo'] == 'save') {
	if(!Validator::characterName($name)) {
		$errors[] = 'Invalid name format.';
	}
	
	if(empty($errors)) {
		$player = new OTS_Player();
		$player->find($name);
		if(!$player->isLoaded()) {
			$errors[] = 'Player with name <b>'.$name.'</b> doesn\'t exist.';
		}
		else
		{
			$rank_of_player = $player->getRank();
			if($rank_of_player->isLoaded()) {
				$errors[] = 'Character with name <b>'.$name.'</b> is already in guild. You must leave guild before you join other guild.';
			}
		}
	}
}

if(isset($_REQUEST['todo']) && $_REQUEST['todo'] == 'save') {
	if(empty($errors)) {
		$is_invited = false;
		include(SYSTEM . 'libs/pot/InvitesDriver.php');
		new InvitesDriver($guild);
		$invited_list = $guild->listInvites();
		if(count($invited_list) > 0) {
			foreach($invited_list as $invited) {
				if($invited->getName() == $player->getName()) {
					$is_invited = true;
				}
			}
		}
		
		if(!$is_invited) {
			$errors[] = 'Character '.$player->getName.' isn\'t invited to guild <b>'.$guild->getName().'</b>.';
		}
	}
}
else
{
	if(empty($errors)) {
		$acc_invited = false;
		$account_players = $account_logged->getPlayers();
		include(SYSTEM . 'libs/pot/InvitesDriver.php');
		new InvitesDriver($guild);
		$invited_list = $guild->listInvites();
		
		if(count($invited_list) > 0) {
			foreach($invited_list as $invited) {
				foreach($account_players as $player_from_acc) {
					if($invited->getName() == $player_from_acc->getName()) {
						$acc_invited = true;
						$list_of_invited_players[] = $player_from_acc->getName();
					}
				}
			}
		}
	}
	
	if(!$acc_invited) {
		$errors[] = "Any character from your account isn't invited to <b>" . $guild->getName() . "</b>.";
	}
}
if(!empty($errors)) {
	echo $twig->render('error_box.html.twig', array('errors' => $errors));
	
	echo $twig->render('guilds.back_button.html.twig', array(
		'action' => getLink('guilds') . '/' . $guild_name
	));
}
else {
	if(isset($_REQUEST['todo']) && $_REQUEST['todo'] == 'save') {
		$guild->acceptInvite($player);
		echo $twig->render('success.html.twig', array(
			'title' => 'Accept invitation',
			'description' => 'Player with name <b>'.$player->getName().'</b> has been added to guild <b>'.$guild->getName() . '</b>.',
			'custom_buttons' => $twig->render('guilds.back_button.html.twig', array(
				'action' => getLink('guilds') . '/' . $guild_name
			))
		));
	}
	else
	{
		sort($list_of_invited_players);
		
		echo $twig->render('guilds.accept_invite.html.twig', array(
			'guild_name' => $guild_name,
			'invited_players' => $list_of_invited_players
		));
	}
}

?>