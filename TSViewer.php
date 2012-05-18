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

require_once "vendor/autoload.php";

// Set absolute directory of the server
$rootDirServer = __DIR__ . "/";
$rootDirPublic = "";

// Set HTTP path to the webviewer
if ((int) $_SERVER['SERVER_PORT'] == 80 || (int) $_SERVER['SERVER_PORT'] == 443)
{
    $url = $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
}
else
{
    $url = $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . $_SERVER['PHP_SELF'];
}

if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === '' || $_SERVER['HTTPS'] === "off")
{
    $rootDirPublic = "http://" . $url;
}
else
{
    $rootDirPublic = "https://" . $url;
}

// Replace file names
$rootDirPublic = str_replace("TSViewer.php", "", $rootDirPublic);
$rootDirPublic = str_replace("index.php", "", $rootDirPublic);

$c = new devmx\TSWebViewer\RenderOptions();
$c['root.public'] = $rootDirPublic;
$c['root.server'] = $rootDirServer;

//configure the container
include('config.php');


header("Content-Type: text/html; charset=utf-8");

// Render viewer
try
{
    echo($c['renderer']->renderServer());
}
catch (Exception $ex)
{
    echo(sprintf("<strong>Fatal Error:</strong> %s<br><strong>line: </strong>%s<br><strong>File: </strong>%s<br><strong>Trace: </strong><pre>%s</pre>", $ex->getMessage(), $ex->getLine(), $ex->getFile(), $ex->getTraceAsString()));
}
?>
