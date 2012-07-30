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

namespace devmx\TSWebViewer\Filters;

/**
 * Base class for filters
 * @author Maximilian Narr
 * @since 1.2
 */
abstract class FilterBase
{

    protected $serverInfo;
    protected $channellist;
    protected $clientlist;
    protected $serverGroupList;
    protected $channelGroupList;
    
    /**
     * @var \devmx\TSWebViewer\RenderOptions
     */
    protected $options;

    /**
     * Construct
     * @param mixed $serverInfo serverInfo received by TeamSpeak3Query
     * @param mixed $channellist channellist received by TeamSpeak3Query
     * @param mixed $clientlist clientlist received by TeamSpeak3Query
     * @param mixed $serverGroupList servergrouplist received by TeamSpeak3Query
     * @param mixed $channelGroupList channelgrouplist received by TeamSpeak3Query
     * @param \devmx\TSWebViewer\RenderOptions $options Options
     * @author Maximilian Narr
     * @since 1.2
     */
    public function __construct($serverInfo, $channellist, $clientlist, $serverGroupList, $channelGroupList, \devmx\TSWebViewer\RenderOptions $options)
    {
        $this->serverInfo = $serverInfo;
        $this->channellist = $channellist;
        $this->clientlist = $clientlist;
        $this->serverGroupList = $serverGroupList;
        $this->channelGroupList = $channelGroupList;

        $this->options = $options;
    }

    /**
     * Filters serverinfo
     * @author Maximilian Narr
     * @since 1.2
     * @return mixed filtered data
     */
    public function filterServerInfo()
    {
        return $this->serverInfo;
    }

    /**
     * Filters channellist
     * @author Maximilian Narr
     * @since 1.2
     * @return mixed filtered data
     */
    public function filterChannellist()
    {
        return $this->channellist;
    }

    /**
     * Filters clientlist
     * @author Maximilian Narr
     * @since 1.2
     * @return mixed filtered data
     */
    public function filterClientlist()
    {
        return $this->clientlist;
    }

    /**
     * Filters servergrouplist
     * @author Maximilian Narr
     * @since 1.2
     * @return mixed filtered data
     */
    public function filterServerGroupList()
    {
        return $this->serverGroupList;
    }

    /**
     * Filters channelgrouplist
     * @author Maximilian Narr
     * @since 1.2
     * @return mixed filtered data
     */
    public function filterChannelGroupList()
    {
        return $this->channelGroupList;
    }
}

?>
