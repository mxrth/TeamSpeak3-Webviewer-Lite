<?php

namespace devmx\TSWebViewer;

/**
 * Provides rendering options for \devmx\TSWebViewer\TSWebViewer;
 * @author Maximilian Narr
 * @since 1.0
 */
class RenderOptions
{

    protected $headTags = false;
    protected $renderServerQueryClients = false;
    protected $connectLink = true;
    protected $connectLinkTarget = false;
    protected $stylesheetURL = null;
    protected $imagePath = null;
    protected $divClass = "devmx-webviewer";
    protected $HTMLCaching = false;
    protected $HTMLCachingPath = "cache";
    protected $HTMLCachingTime = 180;
    protected $imageCaching = false;
    protected $imageCachingPathServer = null;
    protected $imageCachingPathPublic = null;

    /**
     * Sets/ Gets if the viewer should be output as a standalone html page
     * @param bool|null $use If the viewer should be output in a standalone html page. If $use = null it returns the current value of $headTags
     * @return bool true if the viewer should be output in a standalone html page, else false. Returns nothing if $use is not specified
     * @since 1.0
     * @author Maximilian Narr
     */
    public function headTags($use = null)
    {
        if (empty($use)) return $this->headTags;
        else $this->headTags = $use;
    }

    /**
     * If ServerQueryClients should be rendered by the viewer
     * @param bool|null $render If ServerQueryClients should be rendered by the viewer. If $render = null it returns the current value of $renderServerQueryClients;
     * @return mixed true if ServerQueryClients should be rendered else false. Returns nothign if $render is not specified
     * @since 1.0
     * @author Maximilian Narr
     */
    public function renderServerQueryClients($render = null)
    {
        if (empty($render)) return $this->renderServerQueryClients;
        else $this->renderServerQueryClients = $render;
    }

    /**
     * If a link should be applied to the servername that you can directly connect to the server
     * @param bool $addLink If a link should be applied to the servername [optional]
     * @return bool If a link should be applied. Returns nothing if $addLink is set.
     * @since 1.0
     * @author Maximilian Narr
     */
    public function connectLink($addLink = null)
    {
        if (empty($addLink)) return $this->connectLink;
        else $this->connectLink = $addLink;
    }

    /**
     * If you have set 127.0.0.1 or localhost in the main config, you can set here the public ip of the server
     * @param string $linkTarget Public ip adress or hostname of the teamspeak server
     * @return string|bool If you set a custom adress before this will be returned else it returns false. If $linkTarget is set it returns nothing
     * @since 1.0
     * @author Maximilian Narr
     */
    public function connectLinkTarget($linkTarget = null)
    {
        if (empty($linkTarget))
        {
            if (is_string($this->connectLinkTarget)) return $this->connectLinkTarget;
            else return false;
        }
        else $this->connectLinkTarget = (string)$linkTarget;
    }

    /**
     * URL to the stylesheet, the viewer should use
     * @param string|null $url URL to the stylesheet. If $url = null it returns the current value of $stylesheetURL
     * @return string URL to the stylesheet. Returns nothing if $url is not specified
     * @since 1.0
     * @author Maximilian Narr
     */
    public function stylesheetURL($url = null)
    {
        if (empty($url)) return $this->stylesheetURL;
        else $this->stylesheetURL = $url;
    }

    /**
     * Path to the standard group images. The images must be in format 100.png, 200.png, 300.png, 500.png and 600.png
     * @param string|null $path Path to the images. If $path = null it returns the current value of $imgPath
     * @return string Path to images. Returns nothing if $path is not specified
     * @since 1.0
     * @author Maximilian Narr
     */
    public function imgPath($path = null)
    {
        if (empty($path)) return $this->imagePath;
        else
        {
            if (substr($path, -1) != "/") $path .= "/";
            $this->imagePath = $path;
        }
    }

    /**
     * Class of the div around the viewer
     * @param string|null $class name of the class. If $class = null it returns the current value of $divClass 
     * @return string name of the class. Returns nothing if $class is not specified
     * @since 1.0
     * @author Maximilian Narr
     */
    public function divClass($class = null)
    {
        if (empty($class)) return $this->divClass;
        else $this->divClass = $class;
    }

    /**
     * If HTML output should be cached
     * @param bool|null $enabled If HTML output should be cached. If $enabled = null it returns the current value of $HTMLCaching
     * @return bool If HTML caching is enabled. Returns nothing if $enabled is not specified
     * @since 1.0
     * @author Maximilian Narr
     */
    public function HTMLCaching($enabled = null)
    {
        if (empty($enabled)) return $this->HTMLCaching;
        else $this->HTMLCaching = $enabled;
    }

    /**
     * Path where the HTML should be cached
     * @param string|null $path Path where the HTML should be cached. If $HTMLCachingPath = null it returns the current value of $HTMLCachingPath
     * @return string Path where HTML should be cached. Returns nothing if $path is not specified
     * @since 1.0
     * @author Maximilian Narr
     */
    public function HTMLCachingPath($path = null)
    {
        if (empty($path)) return $this->HTMLCachingPath;
        else $this->HTMLCachingPath = $path;
    }

    /**
     * How long the HTML should be cached
     * @param int|null $time Time in seconds how long the HTML should be cached. If $time = null it returns the current value of $HTMLCachingTime
     * @return int Time in seconds how long the HTML Should be cached. Returns nothing if $time is not specified
     * @since 1.0
     * @author Maximilian Narr
     */
    public function HTMLCachingTime($time = null)
    {
        if (empty($time)) return $this->HTMLCachingTime;
        else $this->HTMLCachingTime = $time;
    }

    /**
     * If image caching should be used. Default: no
     * @param bool|null $use True if image caching should be used, else false
     * @return bool If image caching should be used
     * @since 1.0
     * @author Maximilian Narr
     */
    public function imageCaching($use = null)
    {
        if (empty($use)) return $this->imageCaching;
        else $this->imageCaching = $use;
    }

    /**
     * Path to the image cache. The path must be on server side
     * @param string|null $path Serverside path to the image cache. If not null $path is the path where downloaded images should be cached. 
     * @return string Path of the image cache. Returns nothing if $path is not specified
     * @since 1.0
     * @author Maximilian Narr
     * @example /var/www/imagecache/
     */
    public function imageCachingPathServer($path = null)
    {
        if (empty($path)) return $this->imageCachingPathServer;
        else
        {
            if (substr($path, -1) !== "/") $this->imageCachingPathServer = $path . "/";
            else $this->imageCachingPathServer = $path;
        }
    }

    /**
     * Path to the image cache which is accessible by the public
     * @param string|null $path Public path to the image cache. If not null $path is the path where downloaded images should be cached
     * @return string Public path of the image cache. Returns nothing if $path is not specified
     * @since 1.0
     * @author Maximilian Narr
     * @example http://example.com/imagecache/
     */
    public function imageCachingPathPublic($path = null)
    {
        if (empty($path)) return $this->imageCachingPathPublic;
        else
        {
            if (substr($path, -1) !== "/") $this->imageCachingPathPublic = $path . "/";
            else $this->imageCachingPathPublic = $path;
        }
    }

}

?>
