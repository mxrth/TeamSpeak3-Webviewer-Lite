<?php
/**
 *  This file is part of devMX TS3 Webviewer Lite.
 *  Copyright (C) 2012 Maxe <maxe.nr@live.de>
 *  Copyright (C) 2012 drak3 <drak3@live.de>
 *
 *  devMX Webviewer Lite is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  TeamSpeak3 Webviewer is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with devMX TS3 Webviewer Lite.  If not, see <http://www.gnu.org/licenses/>.
 */



// ### BASIC CONFIGURATION ###


/*
 * Hostname or ip adress of your teamspeak3 server
 * e.g. example.com or 192.168.178.2 
 */
$c['ts3']['host'] = 'localhost';


/**
 * Queryport of your teamspeak3 server
 * default: 10011
 */
$c['ts3']['query.port'] = 10011;


/**
 * Serverport of the teamspeak3 server to display
 * default: 9987 --> 
 */
$c['ts3']['vserver.port'] = 9987;


/**
 * Username of the query user [optional]
 * default: empty 
 */
$c['ts3']['login.name'] = '';


/**
 * Password of the query user [optional]
 * default: emtpy 
 */
$c['ts3']['login.pass'] = '';



// ### Advanced configuration ###


/**
 * If the viewer should be cached (recommended)
 * default: true
 */
$c['enable_caching'] = false;


/**
 * If the viewer should download custom icons (recommended) -->
 * default: true 
 */
$c['download_custom_icons'] = true;


/**
 * If images should be shown (recommended)
 * default: true
 */
$c['images.show'] = true;


/**
 *  If country icons should be shown (not recommended)
 *  default: false
 */
$c['country_icons.show'] = true;


/**
 * Name of the style which should be used
 * Currently available:
 *  - base (default style)
 *  - modern-dark (ported from the big devMX Webviewer)
 */
$c['style'] = "base";


/**
 * If query clients should be rendered in the viewer (not recommended)
 * default: false
 */
$c['render.query_clients'] = false;


/**
 * If whitelist rendering should be enabled
 */
$c['filter.whitelist.enabled'] = false;


/**
 * Array of channel-ids of channels which should be rendered.
 * All other channels WONT be rendered
 */
$c['filter.whitelist'] = array();


/**
 * If blacklist rendering should be enabled
 */
$c['filter.blacklist.enabled'] = false;


/**
 * Array of channel-ids of channel which should NOT be rendered.
 * All other channels WILL BE rendered
 */
$c['filter.blacklist'] = array();
?>
