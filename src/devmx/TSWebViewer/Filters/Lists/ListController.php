<?php

/**
 * This file is part of the Teamspeak3 ChannelWatcher.
 * Copyright (C) 2012 drak3 <drak3@live.de>
 * Copyright (C) 2012 Maxe <maxe.nr@live.de>
 * 
 * The Teamspeak3 ChannelWatcher is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The Teamspeak3 ChannelWatcher is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with the Teamspeak3 ChannelWatcher.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */

namespace devmx\TSWebViewer\Filters\Lists;

/**
 *
 * @author drak3
 */
class ListController {

    protected $blacklist;
    protected $whitelist;

    /**
     * @param array $blacklist canAccess returns false for all items which are in the blacklist 
     * @param array|null $whitelist if a array is passed canAccess returns false for items which are not in the whitelist
     */
    public function __construct(array $blacklist = array(), $whitelist = null) {
        $this->blacklist = $blacklist;
        $this->whitelist = $whitelist;
    }

    /**
     * Tests if item is accessable
     * this is determined with the help of the black and whitelist
     * @param mixed $item
     * @return boolean 
     */
    public function canAccess($item) {
        if (is_array($this->whitelist) && !in_array($item, $this->whitelist)) {
            return false;
        }
        if (in_array($item, $this->blacklist)) {
            return false;
        }
        return true;
    }

}