<?php
/*
 * Plugin: Social Media
 * ~~~~~~~~~~~~~~~~~~~~
 * » A buttons Widget for social Media and more.
 *
 * ----------------------------------------------------------------------------------
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ----------------------------------------------------------------------------------
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginSocialMedia();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginSocialMedia extends Plugin {
	public $config;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('tmf.ramires');
		$this->setVersion('1.0.0');
		$this->setBuild('2017-06-07');
		$this->setCopyright('2017 by tmf.ramires');
		$this->setDescription('A buttons Widget for social media and more.');

		$this->registerEvent('onSync',					'onSync');
		$this->registerEvent('onPlayerConnect',			'onPlayerConnect');
		$this->registerEvent('onLoadingMap',			'onLoadingMap');
		$this->registerEvent('onRestartMap',			'onRestartMap');
		$this->registerEvent('onEndMapPrefix',			'onEndMapPrefix');

		$this->registerChatCommand('socialreload',		'chat_socialreload',	'Reload the "Social Media" settings.',	Player::MASTERADMINS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Check for the right UASECO-Version
		$uaseco_min_version = '0.9.0';
		if (defined('UASECO_VERSION')) {
			if ( version_compare(UASECO_VERSION, $uaseco_min_version, '<') ) {
				trigger_error('[SocialMedia] Not supported USAECO version ('. UASECO_VERSION .')! Please update to min. version '. $uaseco_min_version .'!', E_USER_ERROR);
			}
		}
		else {
			trigger_error('[SpcialMedia] Can not identify the System, "UASECO_VERSION" is unset! This plugin runs only with UASECO/'. $uaseco_min_version .'+', E_USER_ERROR);
		}

		// Read Configuration
		if (!$xml = $aseco->parser->xmlToArray('config/social_media.xml', true, true)) {
			trigger_error('[SocialMedia] Could not read/parse config file "config/social_media.xml"!', E_USER_ERROR);
		}
		$this->config = $xml['SETTINGS'];
		unset($xml);

		$this->config['Widget']['Race'] = $this->loadTemplate('Race');
		$this->config['Widget']['Score'] = $this->loadTemplate('Score');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function loadTemplate ($state) {

		// Setup Widget
		$xml = '<manialink name="PluginSocialMediaWidget" id="PluginSpcialMediaWidget" version="3">';
		$xml .= '<stylesheet>';
		$xml .= '<style class="labels" textsize="1" scale="1" textcolor="FFFF"/>';
		$xml .= '</stylesheet>';

		// Figure out how many commands where added
		$command_amount = count($this->config['COMMANDS'][0]['ENTRY']);
		$command_amount = (($command_amount > 10) ? 10 : $command_amount);
		if ($command_amount == 1) {
			$command_amount = 2;
		}

		// SET WIDTH AND HEIGHT
		$height = $this->config['WIDGET'][0]['HEIGHT'][0];
		$width = $this->config['WIDGET'][0]['WIDTH'][0];

		if ($state == 'Race') {
			$posx = -(160 + ($command_amount * ($width + 0.75)) + 14.25);
			$xml .= '<frame pos="'. $posx .' '. $this->config['WIDGET'][0]['RACE'][0]['POS_Y'][0] .'" z-index="20" id="SocialMediaFrame">';
		}
		else {
			$posx = -(160 + ($command_amount * ($width + 0.75)) + 14.25);
			$xml .= '<frame pos="'. $posx  .' '. $this->config['WIDGET'][0]['SCORE'][0]['POS_Y'][0] .'" z-index="20" id="SocialMediaFrame">';
		}

		$xml .= '<quad pos="0 0" z-index="0.001" size="'. (($command_amount * ($width + 0.75)) + 25) .' ' . ($height + 5) .'" bgcolor="55556699" bgcolorfocus="555566BB" ScriptEvents="1"/>';
		$xml .= '<quad pos="'. (($command_amount * ($width + 0.75)) + 16.5) .' -' . ($height / 2) .'" z-index="0.002" size="6 6" style="'. $this->config['WIDGET'][0]['ICON_STYLE'][0] .'" substyle="'. $this->config['WIDGET'][0]['ICON_SUBSTYLE'][0] .'"/>';
		$xml .= '<label pos="'. (($command_amount * ($width + 0.75)) + 14.25) .' -' . ($height + 2) .'" z-index="0.1" size="37.5 0" class="labels" halign="right" textcolor="FC0F" scale="0.8" text="Social-Media/'. $this->getVersion() .'" url="https://github.com/tmframires/uaseco-social-media"/>';
		$xml .= '<quad pos="4 -' . ($height / 2) .'" z-index="0.2" size="8.75 6.6" style="Icons128x128_1" substyle="BackFocusable" ScriptEvents="1"/>';

		$offset = 14.25; //Distance to the left arrow
		$col = 0;
		$command_count = 0;
		foreach ($this->config['COMMANDS'][0]['ENTRY'] as $item) {
			if (empty($item['ICON_FOCUS'][0])) {
				$xml .= '<quad pos="'. ($col + $offset) .' -0.9375" z-index="0.3" size="' . $width .' '. $height .'" url="' . $item['URL'][0] . '" image="'. $item['ICON'][0] .'" id="SocialMedia'. $command_count .'" ScriptEvents="1"/>';
			} else {
				$xml .= '<quad pos="'. ($col + $offset) .' -0.9375" z-index="0.3" size="' . $width .' '. $height .'" url="' . $item['URL'][0] . '" image="'. $item['ICON'][0] .'" imagefocus="'. $item['ICON_FOCUS'][0] .'" id="SocialMedia'. $command_count .'" ScriptEvents="1"/>';
			}
			$col += $width + 0.75;

			// Limited to 10 entries
			if ($command_count >= 9) {
				break;
			}

			$command_count ++;
		}
		$xml .= '</frame>';

$xml .= <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Function:	Widget @ plugin.social_media.php
 * Author:	tmf.ramires
 * Website:	http://www.sachalehmann.de
 * License:	GPLv3
 * ----------------------------------
 */
#Include "TextLib" as TextLib
Void Scrolling(Text ChildId, Boolean Direction) {
	declare CMlFrame Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);
	declare Real PositionClosed = {$posx};
	declare Real PositionOpen = -163.5;
	declare Real Distance = (PositionClosed - PositionOpen);
	if (Direction == True) {
		while (Container.PosnX > PositionClosed) {
			Container.PosnX += (Distance / 10);
			yield;
		}
		Container.PosnX = PositionClosed;
	}
	else {
		while (Container.PosnX < PositionOpen) {
			Container.PosnX -= (Distance / 20);
			yield;
		}
		Container.PosnX = PositionOpen;
	}
}
main() {
	declare Boolean WindowState = False;
	declare Integer AutoCloseTimer = 0;
	while (True) {
		foreach(Event in PendingEvents) {
			switch(Event.Type) {
				case CMlEvent::Type::MouseClick : {
					if (WindowState == False) {
						WindowState = True;
						AutoCloseTimer = (CurrentTime + 7000);
						Scrolling("SocialMediaFrame", False);
					}
					else if (WindowState == True) {
						WindowState = False;
						AutoCloseTimer = 0;
						Scrolling("SocialMediaFrame", True);
					}
				}
				case CMlEvent::Type::MouseOver : {
					if (TextLib::SubString(Event.ControlId, 0, 11) == "SocialMedia") {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
					}

					AutoCloseTimer = (CurrentTime + 7000);
				}
			}
		}
		if ( (AutoCloseTimer != 0) && (CurrentTime >= AutoCloseTimer) ) {
			WindowState = False;
			AutoCloseTimer = 0;
			Scrolling("SocialMediaFrame", True);
		}
		yield;
	}
}
--></script>
EOL;
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_socialreload ($aseco, $login, $chat_command, $chat_parameter) {

		// Reload the "social_media.xml"
		$this->onSync($aseco);

		if ($aseco->server->gamestate == Server::RACE) {
			$aseco->sendManialink($this->config['Widget']['Race'], false);
		}
		else if ($aseco->server->gamestate == Server::SCORE) {
			$aseco->sendManialink($this->config['Widget']['Score'], false);
		}

		$aseco->sendChatMessage('{#admin}>> Reload of the configuration "social_media.xml" done.', $login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {
		$aseco->sendManialink($this->config['Widget']['Race'], $player->login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onLoadingMap ($aseco, $map) {
		$aseco->sendManialink($this->config['Widget']['Race'], false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onRestartMap ($aseco, $map) {
		$aseco->sendManialink($this->config['Widget']['Race'], false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMapPrefix ($aseco, $map) {
		$aseco->sendManialink($this->config['Widget']['Score'], false);
	}

}

?>
