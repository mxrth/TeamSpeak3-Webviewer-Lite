<?php

/**
 *  This file is part of devMX TS3 Webviewer Lite.
 *  Copyright (C) 2012  Maximilian Narr <maxe.nr@live.de>
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

namespace devmx\TSWebViewer\Style;

/**
 * Provides a class for styles
 * @author Maximilian Narr
 * @since 1.2
 */
class Style
{

    /**
     * @var \devmx\TSWebViewer\RenderOptions
     */
    protected $options;
    protected $serverPath;
    protected $publicPath;
    public $cssPath;
    public $imagePath;
    public $groupImagePath;
    public $countryImagePath;
    public $countryImagePathLocal;
    public $templatePath;
    public $author;
    public $authorEmail;
    public $dependency;
    public $filetype;

    /**
     * @var \devmx\TSWebViewer\Style\Style
     */
    protected $dependencyObj;

    public function __construct(\devmx\TSWebViewer\RenderOptions $options)
    {
        $this->options = $options;

        $this->serverPath = $this->options['root.server'] . $options['style.path'] . $options['style'] . '/';
        $this->publicPath = $this->options['root.public'] . $options['style.path'] . $options['style'] . '/';

        $this->loadStyle();
    }

    protected function loadStyle()
    {
        $optionsFile = $this->options['style.directory'] . '/' . $this->options['style'] . '/' . $this->options['style'] . '.json';

        if (!file_exists($optionsFile)) throw new \RuntimeException("Optionfile for the selected style does not exist. Style is corrupt.");

        $file = file_get_contents($optionsFile);
        $file = json_decode($file, true);

        if (isset($file['author']['name'])) $this->author = $file['author']['name'];

        if (isset($file['author']['email'])) $this->authorEmail = $file['author']['email'];

        if (isset($file['dependency']) && !empty($file['dependency']))
        {
            $this->dependency = $file['dependency'];

            $newOptions = $this->options;
            $newOptions['style'] = $this->dependency;

            $this->dependencyObj = new \devmx\TSWebViewer\Style\Style($newOptions);
        }

        // Path to css file
        if (isset($file['files']['css']) && !empty($file['files']['css']))
        {
            $this->cssPath = $this->publicPath . $file['files']['css'];
        }
        else
        {
            $this->cssPath = $this->dependencyObj->cssPath;
        }

        // Image path
        if (isset($file['files']['images']) && !empty($file['files']['images']))
        {
            $this->imagePath = $this->publicPath . $file['files']['images'];
        }
        else
        {
            $this->imagePath = $this->dependencyObj->imagePath;
        }

        // Group images
        if (isset($file['files']['group-images']) && !empty($file['files']['group-images']))
        {
            $this->groupImagePath = $this->publicPath . $file['files']['group-images'];
        }
        else
        {
            $this->groupImagePath = $this->dependencyObj->imagePath;
        }

        // Country images
        if (isset($file['files']['countries']) && !empty($file['files']['countries']))
        {
            $this->countryImagePath = $this->publicPath . $file['files']['countries'];
            $this->countryImagePathLocal = $this->serverPath . $file['files']['countries'];
        }
        else
        {
            $this->countryImagePath = $this->dependencyObj->countryImagePath;
            $this->countryImagePathLocal = $this->dependencyObj->countryImagePathLocal;
        }

        if (isset($file['filetype']) && !empty($file['filetype']))
        {
            $this->filetype = $file['filetype'];
        }
        else
        {
            $this->filetype = "png";
        }
    }

}

?>
