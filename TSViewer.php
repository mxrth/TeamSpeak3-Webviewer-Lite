<?php

/**
 *  This file is part of devMX Webviewer Lite.
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
 *  along with devMX Webviewer Lite.  If not, see <http://www.gnu.org/licenses/>.
 */
// Check PHP version
if (version_compare(phpversion(), 5.3, "<")) exit("PHP 5.3 or higher required.");

$rootDir = __DIR__ . "/";

spl_autoload_register(function($class)
        {
            global $rootDir;
            $path = str_replace("\\", "/", $class);
            $path = $rootDir . "lib/" . $path . ".php";
            if (\file_exists($path)) require_once $path;
        });

        
// Create viewer options
$viewerOptions = new devmx\TSWebViewer\RenderOptions();
$viewerOptions->stylesheetURL("css/style.css");
$viewerOptions->imageCaching(true);
$viewerOptions->imageCachingPathPublic("http://testing.devmx.de/maxe/TeamSpeak3-Webviewer-Lite/cache/");
$viewerOptions->imageCachingPathServer("./cache/");
$viewerOptions->HTMLCachingPath("./cache/");
$viewerOptions->HTMLCachingTime(180);
$viewerOptions->HTMLCaching(true);

// Load configuration file
$config = simplexml_load_file($rootDir . "config.xml");

$host = (string)$config->host;
$queryPort = (int)$config->queryport;
$serverPort = (int)$config->serverport;

$username = (string)$config->username;
$password = (string)$config->password;

// Check for empty config variables
if (empty($host) || empty($queryPort) || empty($serverPort)) exit("Not all config variables are filled out. Please check your config.");

// If login is needed
if (!empty($username) && !empty($password))
{
    $viewer = new devmx\TSWebViewer\TSWebViewer($host, $queryPort, $serverPort, $username, $password);
}
// If no login is needed
else
{
    $viewer = new devmx\TSWebViewer\TSWebViewer($host, $queryPort, $serverPort);
}

// Render viewer
try
{
    echo($viewer->renderServer($viewerOptions));
}
catch (Exception $ex)
{
    echo(sprintf("<strong>Fatal Error:</strong> %s<br><strong>line: </strong>%s<br><strong>File: </strong>%s<br><strong>Trace: </strong><pre>%s</pre>",
            $ex->getMessage(), $ex->getLine(), $ex->getFile(),
            $ex->getTraceAsString()));
}
?>
