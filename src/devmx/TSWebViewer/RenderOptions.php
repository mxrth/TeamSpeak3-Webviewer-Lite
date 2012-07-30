<?php

/**
 *  This file is part of devMX TS3 Webviewer Lite.
 *  Copyright (C) 2012  Maximilian Narr
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

namespace devmx\TSWebViewer;
use devmx\TSWebViewer\Caching\FileCache;

/**
 * Provides rendering options for \devmx\TSWebViewer\TSWebViewer;
 * @author Maximilian Narr
 * @since 1.0
 */
class RenderOptions extends \Pimple
{
    
    public function __construct() {
        /**
         * If the viewer should be output as a standalone html page 
         */
        $this['html.generate_head_tags'] = true;
        
        
        /**
         * If ServerQueryClients should be rendered by the viewer 
         */
        $this['render.query_clients'] = false;
        
        
        /**
         * Render options for blacklist
         */
        $this['filter.blacklist.enabled'] = false;
        $this['filter.blacklist'] = array();
        
        
        /**
         * Render options for whitelist
         */
        $this['filter.whitelist.enabled'] = false;
        $this['filter.whitelist'] = array();
        
        
        /**
         * If a link should be applied to the servername that you can directly connect to the server
         */
        $this['connectlink.show'] = true;
        
        
        /**
         * If you have set 127.0.0.1 or localhost in the main config, you can set here the public ip of the server
         */
        $this['connectlink.target'] = null;
        
        
        /**
         * Path of the style directory
         */
        $this['style.directory'] = function($c) {
            return $c['root.server'].'styles/';
        };
        
        /**
         * URL to the stylesheet, the viewer should use
         */
        $this['style'] = "base";

        /**
         * Path to styles
         */
        $this['style.path'] = "styles/";    
        
        /**
         * If any images should be shown in the webviewer
         */
        $this['images.show'] = true;  
        
        /**
         * If country icons should be displayed 
         */
        $this['country_icons.show'] = true;
        
        /**
         * If custom icons should downloaded from the ts server
         */
        $this['download_custom_icons'] = true;
        
        
        /**
         * Class of the div around the viewer
         */
        $this['html.div_class'] = "devmx-webviewer";
        
        
        /**
         * If caching should be enabled
         */
        $this['enable_caching'] = false;
        
        
        /**
         * If HTML output should be cached
         */
        $this['cache.html.enable'] = function($c){return $c['enable_caching'];};
        
        
        /**
         * The cache path 
         */
        $this['cache.file.path'] = function($c) {
            return $c['root.server'].'cache';
        };
        
        
        /**
         * The file chacheHandler 
         */
        $this['cache.file'] = $this->share(function($c){
            return new FileCache($c['cache.file.path']);
        });
        
        
        /**
         * Handler which handles the HTML Caching
         * Defaults to a file cache
         */
        $this['cache.html'] = $this->raw('cache.file');
        
        
        /**
         * If Image caching should be enabled 
         */
        $this['cache.images.enable'] = function($c) {return $c['enable_caching'];};
        
        
        /**
         * The image cache handler 
         */
        $this['cache.images'] = $this->raw('cache.file');
        
        $this['renderer'] = $this->share(function($c) {
            return new \devmx\TSWebViewer\TSWebViewer($c['ts3']['query'], $c);
        });
        
        
        /**
         * Preconfigured ts3 container
         * needs the host, the query.port
         */
        $this['ts3'] = new \devmx\Teamspeak3\SimpleContainer;
        $this['ts3']['query.transport.decorators']['order'] = array(); //we don't need any decorators;
        $this['ts3']['query'] = $this->share($this['ts3']->extend('query',function($query,$c) {
            $query->connect();
            if($c['login.name'] !== '') {
                $query->login($c['login.name'], $c['login.pass']);
            }
            $query->useByPort($c['vserver.port']);
            return $query;
        }));
    }  

}

?>
