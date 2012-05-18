<?php

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


//### Advanced configuration ### --> 

/**
 * If the viewer should be cached (recommended)
 * default: true
 */
$c['enable_caching'] = true;

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
 * If query clients should be rendered in the viewer (not recommended)
 * default: false
 */
$c['render_serverquery_clients'] = false;
?>
