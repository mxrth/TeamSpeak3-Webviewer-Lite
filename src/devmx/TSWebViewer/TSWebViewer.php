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

use devmx\Teamspeak3\Query\Transport\QueryTransport;
use devmx\Teamspeak3\Query\Transport\TransportInterface;
use devmx\Transmission\TCP;
use devmx\Teamspeak3\Query\Transport\Common\ResponseHandler;
use devmx\Teamspeak3\Query\Transport\Common\CommandTranslator;

class TSWebViewer
{

    private $options;
    
    /**
     * @var devmx\TeamSpeak3\Query\Transport\QueryTransport;
     */
    private $query;

    /**
     * @var devmx\Transmission\TCP; 
     */
    private $tcp;

    /**
     * @var devmx\Teamspeak3\Query\CommandResponse;
     */
    private $serverinfo;

    /**
     * @var devmx\Teamspeak3\Query\CommandResponse;
     */
    private $channellist;

    /**
     * @var devmx\Teamspeak3\Query\CommandResponse;
     */
    private $clientlist;

    /**
     * @var devmx\Teamspeak3\Query\CommandResponse;
     */
    private $serverGroupList;

    /**
     * @var devmx\Teamspeak3\Query\CommandResponse;
     */
    private $channelGroupList;

    /**
     * @var devmx\TSWebViewer\RenderOptions;
     */
    private $renderOptions;
    private $renderTimeStart;
    private $renderTimeEnd;

    function __construct(  TransportInterface $query, $options)
    {
        $this->query = $query;
        $this->options = $options;
    }

    /**
     * Closes connection to the server 
     * @since 1.0
     * @author Maximilian Narr
     */
    private function closeConnection()
    {
        $this->query->query("quit");
    }

    /**
     * Tries to receive the data needed for the viewer to build
     * @throws \RuntimeException If an error occurs receiving data
     * @since 1.0
     * @author Maximilian Narr
     */
    private function getServerData()
    {
        $this->serverinfo = $this->query->query("serverinfo");
        if ($this->serverinfo->errorOccured()) throw new \RuntimeException("Error receiving serverinfo. " . $this->serverinfo->getErrorMessage());

        $this->channellist = $this->query->query("channellist", array(), array("limits", "flags", "voice", "icon"));
        if ($this->channellist->errorOccured()) throw new \RuntimeException("Error receiving channellist. " . $this->channellist->getErrorMessage());

        $this->clientlist = $this->query->query("clientlist", array(), array("away", "voice", "info", "icon", "groups", "country"));
        if ($this->clientlist->errorOccured()) throw new \RuntimeException("Error receiving clientlist. " . $this->clientlist->getErrorMessage());

        $this->serverGroupList = $this->query->query("servergrouplist");
        if ($this->serverGroupList->errorOccured()) throw new \RuntimeException("Error receiving servergouplist. " . $this->serverGroupList->getErrorMessage());

        $this->channelGroupList = $this->query->query("channelgrouplist");
        if ($this->channelGroupList->errorOccured()) throw new \RuntimeException("Error receiving channelgrouplist. " . $this->channelGroupList->getErrorMessage());
    }

    /**
     * Renders the server
     * @return string HTML-Code for the Webviewer
     * @throws RuntimeException If error occures
     * @since 1.0
     * @author Maximilian Narr
     */
    public function renderServer()
    {
        $this->renderTime = microtime(true);

        // If caching should be used
        if ($this->options['cache.html.enable'])
        {
            $HTMLCachingHandler = $this->options['cache.html'];
            $key = md5($this->options['ts3']['host'] . $this->options['ts3']['query.port'] . $this->options['ts3']['vserver.port']);

            if ($HTMLCachingHandler->isCached($key)) return $HTMLCachingHandler->getCache($key);
        }

        try
        {
            $this->getServerData();

            // Sort clientlist
            $this->sortClientList();
        }
        catch (\RuntimeException $ex)
        {
            throw $ex;
        }

        $head = '<!DOCTYPE html><html><head><link rel="stylesheet" type="text/css" href="%s" /></head><body>%s</body></html>';
        $div = '<div class="%s">%s</div>';
        $link = '<link rel="stylesheet" type="text/css" href="%s">';

        $html = "";

        // If head-tags should be used
        if ($this->options['html.generate_head_tags'])
        {
            $div = sprintf($div, $this->options['html.div_class'], $this->renderServerName() . $this->getChannels(0));
            $html .= sprintf($head, $this->options['stylesheet.url'], $div);
        }
        // If no head-tags should be used
        else
        {
            // If stylesheetlink should be included
            $stylesheet = $this->options['stylesheet.url'];
            if (!empty($stylesheet))
            {
                $link = sprintf($link, $this->options['stylesheet.url']);
                $html .= sprintf($div, $this->options['html.div_class'], $link . $this->renderServerName() . $this->getChannels(0));
            }
            // If no stylesheet should be included
            else
            {
                $html .= sprintf($div, $this->options['html.div_class'], $this->renderServerName() . $this->getChannels(0));
            }
        }

        // If file needs to be cached
        if ($this->options['cache.html.enable'])
        {
            $HTMLCachingHandler->cache(md5($this->options['ts3']['host'] . $this->options['ts3']['query.port']. $this->options['ts3']['vserver.port']), $html);
        }

        $this->renderTimeEnd = microtime(true);
        $this->closeConnection();

        return $html;
    }

    /**
     * Renders the servername
     * @return string html code containing servername
     * @since 1.0
     * @author Maximilian Narr
     */
    private function renderServerName()
    {
        $serverItem = $this->serverinfo->getItem(0);

        if ($this->options['connectlink.show'])
        {
            $html = '%s<span class="ts-image server-image">&nbsp;</span><a href="%s"><span class="servername">%s</span></a>';
            $tsLink = 'ts3server://%s?port=%s';
            $targetLink = $this->options['connectlink.target'];

            if (!$targetLink)
            {
                $tsLink = sprintf($tsLink, $this->options['ts3']['host'], $this->options['ts3']['vserver.port']);
            }
            else
            {
                $tsLink = sprintf($tsLink, $targetLink, $this->options['ts3']['vserver.port']);
            }

            return sprintf($html, $this->renderServerIcon($serverItem), $tsLink, $this->Utf8ToHtml($serverItem['virtualserver_name']));
        }
        else
        {
            $html = '%s<span class="ts-image server-image">&nbsp;</span><span class="servername">%s</span>';
            return sprintf($html, $this->renderServerIcon($serverItem), $this->Utf8ToHtml($serverItem['virtualserver_name']));
        }
    }

    /**
     * Renders the servers icon and returns formatted HTML code
     * @param type $serverItem
     * @return string|null If server has a icon it returns the HTML code to include the icon, else it returns null. If $showImages is false it returns an empty string.
     * @since 1.0
     * @author Maximilian Narr
     */
    private function renderServerIcon($serverItem)
    {
        if (!$this->options['images.show']) return '';
        else if ($serverItem['virtualserver_icon_id'] != 0) return $this->renderIcon($serverItem['virtualserver_icon_id']);
    }

    /**
     * Renders recursively all channels, subchannels and clients of a channel with cid $cid
     * @param type $cid ChannelId
     * @return string rendered channels, subchannels and clients of channel with cid $cid
     * @since 1.0
     * @author Maximilian Narr
     */
    private function getChannels($cid)
    {
        $items = $this->channellist->getItems();

        $html = "";

        foreach ($items as $channel)
        {
            if ($channel['pid'] == $cid)
            {
                $html .= '<div class="channel">';

                // Check if channel is a spacer
                if ($channel['pid'] !== 0 || !$this->isSpacer($channel)) $html .= sprintf('%s<span class="ts-image %s">&nbsp;</span><span class="label">%s</span>', $this->getChannelImages($channel), $this->getChannelStatusImage($channel), $this->Utf8ToHtml($channel['channel_name']));
                else $html .= $this->parseSpacer($channel);

                $html .= $this->getClients($channel['cid']);
                $html .= $this->getChannels($channel['cid']);
                $html .= '</div>';
            }
        }

        return $html;
    }

    /**
     * Renders all clients of channel with cid $cid
     * @param type $cid ChannelId
     * @return string rendered clients in channel with cid $cid
     * @since 1.0
     * @author Maximilian Narr
     */
    private function getClients($cid)
    {
        $items = $this->clientlist->getItems();

        $html = "";

        foreach ($items as $client)
        {
            if ($client['cid'] == $cid)
            {
                // Skip client if ServerQueryClient
                if (!$this->options['render_serverquery_clients'] && $client['client_type'] == (int) 1) continue;

                $html .= '<div class="client">';
                $html .= sprintf('%s<span class="ts-image %s">&nbsp;</span><span class="label">%s</span>', $this->getClientImages($client), $this->getClientStatusImage($client), $this->Utf8ToHtml($client['client_nickname']));
                $html .= '</div>';
            }
        }

        return $html;
    }

    /**
     * Checks if the channel is full or has a password and returns the corresponding css class
     * @param type $channelItem 
     * @return string corresponding css class
     * @since 1.0
     * @author Maximilian Narr
     */
    private function getChannelStatusImage($channelItem)
    {
        if ($channelItem['channel_flag_password'] == (int) 1) return "channel-password";
        else if ($channelItem['total_clients'] == $channelItem['channel_maxclients']) return "channel-full";
        else return "channel-normal";
    }

    /**
     * Checks the channel $channelItem for icons:
     * Home icon, music icon, moderated icon and channel icon
     * @param type $channelItem Channel item to check for icons
     * @return string Formatted html with the containg icons. If $showImages is false it returns an empty string
     * @since 1.0
     * @author Maximilian Narr
     */
    private function getChannelImages($channelItem)
    {
        $style = '<span class="ts-image image-right %s">&nbsp;</span>';
        $data = "";

        // Check if displaying if images is disabled
        if (!$this->options['images.show'])
        {
            return '';
        }

        // Check if channel has a password
        if ($channelItem['channel_flag_password'] == 1)
        {
            $data .= sprintf($style, "channel-password-right");
        }

        // Check if channel is home channel
        if ($channelItem['channel_flag_default'] == 1)
        {
            $data .= sprintf($style, "channel-home") . $data;
        }

        // Check for 48khz codec
        if ($channelItem['channel_codec'] == 3)
        {
            $data = sprintf($style, "channel-48khz") . $data;
        }

        // Check if channel is moderated
        if ($channelItem['channel_needed_talk_power'] > 0)
        {
            $data = sprintf($style, "channel-moderated") . $data;
        }

        // Check for custom icon
        if ($channelItem['channel_icon_id'] != 0)
        {
            $data = $this->renderIcon($channelItem['channel_icon_id']) . $data;
        }

        return $data;
    }

    /**
     * Checks if the client is away or has anything muted and returns the corresponding css class
     * @param type $clientItem 
     * @return string corresponding css class
     * @since 1.0
     * @author Maximilian Narr
     */
    private function getClientStatusImage($clientItem)
    {
        if ($clientItem['client_type'] == (int) 1) return "client-query";
        else if ($clientItem['client_away'] == (int) 1) return "client-away";
        else if ($clientItem['client_input_muted'] == (int) 1) return "client-input-muted";
        else if ($clientItem['client_output_muted'] == (int) 1) return "client-output-muted";
        else if ($clientItem['client_input_hardware'] == (int) 0) return "client-input-muted-hardware";
        else if ($clientItem['client_output_hardware'] == (int) 0) return "client-output-muted-hardware";
        else if ($clientItem['client_flag_talking'] == (int) 1 && $clientItem['client_is_channel_commander'] == (int) 1) return "client-channel-commander-talking";
        else if ($clientItem['client_is_channel_commander'] == (int) 1) return "client-channel-commander";
        else if ($clientItem['client_flag_talking'] == (int) 1) return "client-talking";
        else return "client-normal";
    }

    /**
     * Gets all client images: servergroupimages, channelgroupimage and clientimage
     * @param type $clientItem
     * @return mixed html code for the images. If $showImages is false it returns an empty string
     * @throws \RuntimeException Throws Runtime-Exceptions on failure
     * @since 1.0
     * @author Maximilian Narr
     */
    private function getClientImages($clientItem)
    {
        $style = '<span class="ts-image image-right %s">&nbsp;</span>';
        $data = "";

        $channelGroupId = $clientItem['client_channel_group_id'];
        $serverGroupIds = explode(",", $clientItem['client_servergroups']);

        $channelGroupIcon = $this->getChannelGroupIconId($channelGroupId);

        // Check if displaying of images is disabled
        if (!$this->options['images.show'])
        {
            return '';
        }

        // Check if client is priority speaker
        if ($clientItem['client_is_priority_speaker'] == 1)
        {
            $data .= sprintf($style, "client-priority-speaker");
        }

        // Check if channelgroup has an icon
        if ($channelGroupIcon != false)
        {
            $data = $this->renderIcon($channelGroupIcon) . $data;
        }

        // Check if Servergroups have icons
        foreach ($serverGroupIds as $serverGroupId)
        {
            $serverGroupIcon = $this->getServerGroupIconId($serverGroupId);

            if ($serverGroupIcon != false) $data = $this->renderIcon($serverGroupIcon) . $data;
        }

        // Check for client icon
        if (!$clientItem['client_icon_id'] == 0)
        {
            $clientIconId = $clientItem['client_icon_id'];

            $data = $this->renderIcon($clientIconId) . $data;
        }

        // If country icons should be used
        if ($this->options['country_icons.show'])
        {
            if ($clientItem['client_country'] !== "")
            {
                $country = $clientItem['client_country'];

                $data = $this->renderCountryIcon($country) . $data;
            }
        }

        return $data;
    }

    /**
     * Gets the iconid of a channel group
     * @param int $channelGroupId channelgroup-id (cgid) of the channel-group
     * @return boolean|int Returns false if the group has no icon (id 0) or the icon id if it has one
     * @since 1.0
     * @author Maximilian Narr
     */
    private function getChannelGroupIconId($channelGroupId)
    {
        foreach ($this->channelGroupList as $channelGroup)
        {
            if ($channelGroup['cgid'] == $channelGroupId)
            {
                if ($channelGroup['iconid'] == 0) return false;
                else return $channelGroup['iconid'];
            }
        }
    }

    /**
     * Gets the iconid of a server group
     * @param int $serverGroupId servergroup-id (sgid) of the server-group
     * @return boolean|int Returns false if the group has no icon (id 0) or the icon id it has one
     * @since 1.0
     * @author Maximilian Narr
     */
    private function getServerGroupIconId($serverGroupId)
    {
        foreach ($this->serverGroupList as $serverGroup)
        {
            if ($serverGroup['sgid'] == $serverGroupId)
            {
                if ($serverGroup['iconid'] == 0) return false;
                else return $serverGroup['iconid'];
            }
        }
    }

    /**
     * Renders a country icon
     * @param string $country two letter country code
     * @return string formatted html to include the country icons. Returns nothing if icons does not exist
     * @since 1.1
     * @author Maximilian Narr
     */
    protected function renderCountryIcon($country)
    {
        $country = strtolower($country);

        $serverPath = $this->options['country_icons.path'];
        $publicPath = $this->options['country_icons.url'];
        $fileType = $this->options['country_icons.filetype'];

        if (is_null($serverPath)) throw new \RuntimeException('$countryIconsPath is not specified. Plase set it.');
        if (is_null($publicPath)) throw new \RuntimeException('$countryIconsUrl is not specified. Please set it.');
        if (is_null($fileType)) throw new \RuntimeException('$fileType is not specified. Please set it.');

        $style = 'background: url(%s) center center no-repeat;';
        $imageHtml = '<span style="%s" class="ts-image image-right">&nbsp;</span>';

        // Check if icons exists
        if (file_exists($serverPath . $country . "." . $fileType))
        {
            $styleTag = sprintf($style, $publicPath . $country . "." . $fileType);
            return sprintf($imageHtml, $styleTag);
        }
    }

    /**
     * Renders the icon with iconId $iconId
     * If the icon is a standard icon (Id 100, 200, 300, 500 or 600),
     * it will return the formatted html to the icon.
     * If the icon has to be downloaded, it will be downloaded 
     * and the formatted html will is returned
     * @param int $iconId iconId of the icon to handle
     * @return string Formatted html to include the icon
     * @throws \RuntimeException If the path to standard icons is not set.
     * @since 1.0
     * @author Maximilian Narr 
     */
    private function renderIcon($iconId)
    {
        $standardImages = array(100, 200, 300, 500, 600);
        $style = 'background-image: url(%s);';

        $imageHtml = '<span style="%s" class="ts-image image-right">&nbsp;</span>';

        // Standard image
        if (in_array($iconId, $standardImages))
        {
            // Check if $imgPath is available
            if ($this->options['images.path'] == null) throw new \RuntimeException('$imgPath is not specified in the renderOptions. Please set it.');

            $styleTag = sprintf($style, $this->options['images.path'] . $iconId . ".png");
            return sprintf($imageHtml, $styleTag);
        }
        // No standard image --> download it
        else
        {
            // If icon download is disabled
            $downloadImages = $this->options['download_custom_icons'];
            if ($downloadImages == false) return "";


            // Check for icon caching
            $css = $this->manageImageCaching($iconId);

            $styleTag = sprintf($style, $css);
            return sprintf($imageHtml, $styleTag);
        }
    }

    /**
     * Handles the caching of the server icon $iconId. If caching is on it caches the icon. If caching is off, it downloads it.
     * @param int $iconId Id of the icon to handle
     * @return string Either base64encoded icon or path to the cached icon
     * @throws \RuntimeException If cache paths are not set
     * @since 1.0
     * @author Maximilian Narr
     */
    private function manageImageCaching($iconId)
    {
        $useImageCaching = $this->options['cache.images.enable'];
        $imagePathPublic = $this->options['cache.file.path']; //TODO: let this not depend on file cache

        $cssDataBase64 = "data:image/png;base64,%s";

        // If no image caching should be used
        if (empty($useImageCaching) || !$useImageCaching)
        {
            return sprintf($cssDataBase64, base64_encode($this->downloadServerIcon($iconId)));
        }
        // If image caching should be used
        else
        {
            $imageCachingHandler = $this->options['cache.images'];

            if (empty($imagePathPublic)) throw new \RuntimeException('$imagePathPublic is not specified in the renderOptions. Please set it.');

            // If image is already cached
            if ($imageCachingHandler->isCached($iconId . ".png"))
            {
                return $imagePathPublic . $iconId . ".png";
            }
            // If it needs to be downloaded
            else
            {
                $img = $this->downloadServerIcon($iconId);
                $imageCachingHandler->cache($iconId . ".png", $img, 0);
                return $imagePathPublic . $iconId . ".png";
            }
        }
    }

    /**
     * Downloads the icon with iconid $iconid from the server
     * @param int $iconid iconid of the icon to download
     * @return string downloaded file
     * @throws \RuntimeException If error occures in query
     * @throws Exception If error occures on download
     * @since 1.0
     * @author Maximilian Narr
     */
    private function downloadServerIcon($iconid)
    {
        // Fix negative ids
        if ($iconid < 0) $iconid = 4294967296 + $iconid;

        try
        {
            $response = $this->query->query("ftinitdownload", array("name" => "/icon_$iconid", "clientftfid" => 0, "cid" => 0, "cpw" => "", "seekpos" => 0));
        }
        catch (Exception $ex)
        {
            throw $ex;
        }

        if ($response->errorOccured()) throw new \RuntimeException("Failed downloading icon. Error:" . $response->getErrorMessage());

        $responseItem = $response->getItem(0);

        $key = $responseItem['ftkey'];
        $fileTransferPort = $responseItem['port'];
        $fileSize = $responseItem['size'];

        try
        {
            // Initialize TCP if not initialized yet
            if ($this->tcp == null) $this->tcp = new \devmx\Transmission\TCP($this->host, $fileTransferPort);

            // Establish TCP if not yet established
            if (!$this->tcp->isEstablished()) $this->tcp->establish();

            $downloader = new \devmx\Teamspeak3\FileTransfer\Downloader($this->tcp, $key, $fileSize);

            $icon = $downloader->transfer();
            $this->tcp->close();
        }
        catch (Exception $ex)
        {
            throw $ex;
        }
        return $icon;
    }

    /**
     * Checks if a channel is a spacer
     * @param type $channelItem Channel to parse
     * @return boolean true if spacer, else false
     * @since 1.0
     * @author Maximilian Narr/ Max Rath
     * @uses https://github.com/devMX/TeamSpeak3-Webviewer/blob/master/core/teamspeak/teamspeak.func.php#L84
     */
    private function isSpacer($channelItem)
    {
        if ($channelItem['pid'] != 0) return false;
        else if (preg_match("#.*\[([rcl*]?)spacer(.*?)\](.*)#", $channelItem['channel_name']) == 0) return false;
        return true;
    }

    /**
     * Parses a spacer and returns its code
     * @param type $channelItem
     * @return mixed html code of the spacer
     * @since 1.0
     * @author Maximilian Narr/ Max Rath
     * @uses https://github.com/devMX/TeamSpeak3-Webviewer/blob/master/TSViewer.php#L501
     */
    private function parseSpacer($channelItem)
    {
        preg_match("#.*\[([rcl*]?)spacer(.*?)\](.*)#", $channelItem['channel_name'], $spacer);

        // Checks if channel is a special spacer
        if (in_array($spacer[3], Array('---', '...', '-.-', '___', '-..')))
        {
            // Special spacer
            $html = '<p class="spacer %s">&nbsp;</p>';

            // Parce special spacer
            switch ($spacer[3])
            {
                case '---':
                    $html = sprintf($html, "spacer-dash");
                    break;
                case '...':
                    $html = sprintf($html, "spacer-point");
                    break;
                case '-.-':
                    $html = sprintf($html, "spacer-line-point");
                    break;
                case '___':
                    $html = sprintf($html, "spacer-line");
                    break;
                case '-..':
                    $html = sprintf($html, "spacer-line-double-point");
                    break;
            }
            return $html;
        }
        else
        {
            // No special spacer
            $html = '<p class="spacer %s">%s</p>';

            // Parse alignment
            switch ($spacer[1])
            {
                case "r":
                    $html = sprintf($html, "spacer-right", $this->Utf8ToHtml($spacer[3]));
                    break;
                case "c":
                    $html = sprintf($html, "spacer-center", $this->Utf8ToHtml($spacer[3]));
                    break;
                case "l":
                    $html = sprintf($html, "spacer-left", $this->Utf8ToHtml($spacer[3]));
                case "*":
                    $html = sprintf($html, "spacer-left spacer-overflow", str_repeat($this->Utf8ToHtml($spacer[3]), 400));
                    break;
                default:
                    $html = sprintf($html, "spacer-left", $this->Utf8ToHtml($spacer[3]));
                    break;
            }
            return $html;
        }
    }

    /**
     * Sorts the clientlist like in the TSClient
     * @since 1.1
     * @author Maximilian Narr
     */
    private function sortClientList()
    {
        $clientlistItems = $this->clientlist->getItems();
        usort($clientlistItems, array($this, 'sortClientListTSStyle'));
        $this->clientlist->setItems($clientlistItems);
    }

    /**
     * Sorting function to sort clientlist like in TSClient
     * @param type $a
     * @param type $b
     * @return int 
     * @see https://github.com/devMX/TeamSpeak3-Webviewer/blob/master/core/teamspeak/teamspeak.func.php#L27
     * @since 1.1
     * @author Max Rath
     */
    private function sortClientListTSStyle($a, $b)
    {
        if ($a['client_talk_power'] > $b['client_talk_power'])
        {
            return -1;
        }
        else if ($a['client_talk_power'] < $b['client_talk_power'])
        {
            return 1;
        }
        else
        {
            if
            ($a['client_is_talker'] == 1 && $b['client_is_talker'] == 0)
            {
                return -1;
            }
            else if
            ($a['client_is_talker'] == 0 && $b['client_is_talker'] == 1)
            {
                return 1;
            }
            else
            {
                return $this->sortClientListByName($a, $b);
            }
        }
    }

    /**
     * Sorting function to sort clientlist by clientname
     * @param type $a
     * @param type $b
     * @return int
     * @see https://github.com/devMX/TeamSpeak3-Webviewer/blob/master/core/teamspeak/teamspeak.func.php#L27
     * @since 1.1
     * @author Max Rath
     */
    private function sortClientListByName($a, $b)
    {
        return strcasecmp($a['client_nickname'], $b['client_nickname']);
    }

    /**
     * Converts UTF8 to HTML
     * @param string $utf8 String to convert
     * @param bool $encodeTags
     * @return string converted string
     * @see http://de3.php.net/manual/de/function.htmlentities.php#96648
     * @author silverbeat
     * @since 1.0
     */
    private function Utf8ToHtml($utf8, $encodeTags = true)
    {
        $result = '';
        for ($i = 0; $i < strlen($utf8); $i++)
        {
            $char = $utf8[$i];
            $ascii = ord($char);
            if ($ascii < 128)
            {
                // one-byte character
                $result .= ( $encodeTags) ? htmlentities($char, ENT_QUOTES) : $char;
            }
            else if ($ascii < 192)
            {
                // non-utf8 character or not a start byte
            }
            else if ($ascii < 224)
            {
                // two-byte character
                $result .= htmlentities(substr($utf8, $i, 2), ENT_QUOTES, 'UTF-8');
                $i++;
            }
            else if ($ascii < 240)
            {
                // three-byte character
                $ascii1 = ord($utf8[$i + 1]);
                $ascii2 = ord($utf8[$i + 2]);
                $unicode = (15 & $ascii) * 4096 +
                        (63 & $ascii1) * 64 +
                        (63 & $ascii2);
                $result .= "&#$unicode;";
                $i += 2;
            }
            else if ($ascii < 248)
            {
                // four-byte character
                $ascii1 = ord($utf8[$i + 1]);
                $ascii2 = ord($utf8[$i + 2]);
                $ascii3 = ord($utf8[$i + 3]);
                $unicode = (15 & $ascii) * 262144 +
                        (63 & $ascii1) * 4096 +
                        (63 & $ascii2) * 64 +
                        (63 & $ascii3);
                $result .= "&#$unicode;";
                $i += 3;
            }
        }
        return $result;
    }

    /**
     * This function checkts if the path provided in $path is writabe
     * @param type $path Path  which should be checked
     * @throws \RuntimeException If a path is not writable.
     * @author Maximilian Narr
     * @since 1.0
     */
    private function checkFilePermissions($path)
    {
        if (!empty($path) && !is_writable($path)) throw new \RuntimeException("$path is not writable. Please check permissions");
    }

    /**
     * Returns the rendertime of the last viewer in microtime format
     * @see http://php.net/manual/en/function.microtime.php
     * @return mixed microtime
     * @since 1.0
     * @author Maximilian Narr
     */
    public function getRenderTime()
    {
        return $this->renderTimeEnd - $this->renderTimeStart;
    }

}

?>