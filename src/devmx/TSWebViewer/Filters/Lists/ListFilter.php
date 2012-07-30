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

namespace devmx\TSWebViewer\Filters\Lists;

class ListFilter extends \devmx\TSWebViewer\Filters\FilterBase
{

    public function filterChannellist()
    {
        $channellist = $this->channellist;
        $items = $channellist->toAssoc('cid');
        
        if (isset($this->options['filter.whitelist.enabled']) && $this->options['filter.whitelist.enabled'] && isset($this->options['filter.blacklist.enabled']) && $this->options['filter.blacklist.enabled'])
        {
            // Whitelist and blacklist enabled
            if(!is_array($this->options['filter.whitelist']) || !is_array($this->options['filter.blacklist']))
                throw new \RuntimeException("Whitelist or blacklist not configured properly. Please check config.");
            
            $controller = new ListController($this->options['filter.blacklist'], $this->options['filter.whitelist']);
        }
        else if (isset($this->options['filter.whitelist.enabled']) && $this->options['filter.whitelist.enabled'])
        {
            // Whitelist only
            if(!is_array($this->options['filter.whitelist']))
                throw new \RuntimeException("Whitelist or blacklist not configured properly. Please check config.");
            
            $controller = new ListController(array(), $this->options['filter.whitelist']);
        }
        else if (isset($this->options['filter.blacklist.enabled']) && $this->options['filter.blacklist.enabled'])
        {
            // Blacklist only
            if(!is_array($this->options['filter.blacklist']))
                throw new \RuntimeException("Whitelist or blacklist not configured properly. Please check config.");
            
            $controller = new ListController($this->options['filter.blacklist']);
        }
        else
        {
            return $this->channellist;
        }
        
        $rule = new ListRule($controller);
        
        $filteredItems = $rule->filter($items);
        
        foreach($filteredItems as $key => $channel)
        {
            if(isset($channel['__delete']) && $channel['__delete'])
                unset($filteredItems[$key]);
        }
        
        $channellist->setItems($filteredItems);
        return $channellist;
    }

}

?>
