<?php

/*
  This file is part of TeamSpeak3 Library.

  TeamSpeak3 Library is free software: you can redistribute it and/or modify
  it under the terms of the GNU Lesser General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  TeamSpeak3 Library is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU Lesser General Public License for more details.

  You should have received a copy of the GNU Lesser General Public License
  along with TeamSpeak3 Library. If not, see <http://www.gnu.org/licenses/>.
 */

declare(encoding = "UTF-8");

namespace devmx\Teamspeak3\Query\Transport\Common;

/**
 * 
 *
 * @author drak3
 */
class ResponseHandler implements \devmx\Teamspeak3\Query\Transport\ResponseHandlerInterface
{
    /**
     * The Length of the message sent by a common query on connect
     */

    const WELCOME_LENGTH = 150;

    /**
     * A string included in the welcomemessage
     */
    const WELCOME_IDENTIFY = "TS3";

    /**
     * The error id returned on success 
     */
    const ID_OK = 0;

    /**
     * The errormessage returned on success
     */
    const MSG_OK = "ok";

    /**
     * The string between two responses/events
     */
    const SEPERATOR_RESPONSE = "\n";

    /**
     * The string between two items (List of data)
     */
    const SEPERATOR_ITEM = "|";

    /**
     * The string between two data packages (e.g. key/value pair)
     */
    const SEPERAOR_DATA = " ";

    /**
     * The string between a key/value pair
     */
    const SEPERATOR_KEY_VAL = "=";

    /**
     * The chars masked by the query and their replacements
     * @var Array 
     */
    protected $unEscapeMap = Array(
        "\\\\" => "\\",
        "\\/" => "/",
        "\\n" => "\n",
        "\\s" => " ",
        "\\p" => "|",
        "\\a" => "\a",
        "\\b" => "\b",
        "\\f" => "\f",
        "\\r" => "\r",
        "\\t" => "\t",
        "\\v" => "\v",
    );

    /**
     * The regular expression for describing a response
     * @var string 
     */
    protected $responseRegex = "/^(.*?[[:blank:]\r\n]?)error id=([0-9]*) msg=([a-zA-Z\\\\]*)( failed_permid=([0-9]*))?$/";

    /**
     * Replaces all masked characters with their regular replacements (e.g. \\ with \)
     * uses $unEscapeMap
     * @param string $string
     * @return string the unmasked string 
     */
    public function unescape($string)
    {
        $string = strtr($string, $this->unEscapeMap);
        return $string;
    }

    /**
     * Parses a response coming from the query for a given command
     * Event notifications occured before sending the command are parsed too
     * @param \devmx\Teamspeak3\Query\Command $cmd
     * @param string $raw
     * @return Array in form Array('response' => $responseObject, 'events' => Array($eventobject1,$eventobject2));  
     */
    public function getResponseInstance(\devmx\Teamspeak3\Query\Command $cmd, $raw)
    {
        $response = Array('response' => NULL, 'events' => Array());
        $parsed = Array();

        $raw = \trim($raw, "\r\n");
        $parsed = \explode(self::SEPERATOR_RESPONSE, $raw);

        $error = \array_pop($parsed); //the last element is our error message
        $response['response'] = $this->parseResponse($cmd, $error);
        foreach($parsed as $part) {
            if(substr($part, 0, strlen($this->getEventPrefix())) === $this->getEventPrefix()) {
                $response['events'][] = $this->parseEvent($part);
            }
            else {
                $response['response'] = $this->parseResponse($cmd, $part.$error);
            }
        }
        

        return $response;
    }
    
    protected function getEventPrefix() {
        return 'notify';
    }

    /**
     * Parses a response (no events in it) for a given command
     * Splits up the response in data and error message and builds a response object
     * @param \devmx\Teamspeak3\Query\Command $cmd
     * @param string $response
     * @return \devmx\Teamspeak3\Query\Response 
     */
    protected function parseResponse(\devmx\Teamspeak3\Query\Command $cmd, $response)
    {
        $parsed = Array();

        preg_match($this->responseRegex, $response, $parsed);

        $errorID = (int) $parsed[2]; // parsed[2] holds the error id
        $errorMessage = $this->unEscape($parsed[3]); //parsed[4] hold the error string

        if ($parsed[1] !== '') // parsed[1] holds the data if it is a fetching command
        {
            $items = $this->parseData($parsed[1]);
        }
        else
        {
            $items = Array();
        }


        if (isset($parsed[4])) //parsed[4] holds the whole key/value pair of the extramessage
        {
            $extra = $parsed[5]; //parsed[5] holds the pure extramessage
        }
        else
        {
            $extra = '';
        }


        $responseClass = new \devmx\Teamspeak3\Query\CommandResponse($cmd, $items, $errorID, $errorMessage, $extra);
        $responseClass->setRawResponse($response);
        return $responseClass;
    }

    /**
     * Parses a single event
     * @param string $event
     * @return \devmx\Teamspeak3\Query\Event 
     */
    protected function parseEvent($event)
    {
        $reason = '';
        $eventObject = NULL;
        $event = explode(self::SEPERAOR_DATA, $event, 2);
        $reason = $this->parseValue($event[0]); //the eventtype or eventreason is a single word at the beginnning of the event
        $event = $event[1];
        $data = $this->parseData($event); //the rest is a single block of data
        $data = $data[0]; //because we have just one block (no |) we can use data[0]


        $eventClass = new \devmx\Teamspeak3\Query\Event($reason, $data);
        $eventClass->setRawResponse($event);
        return $eventClass;
    }

    /**
     * parses the data of a event or response.
     * First splits up in blocks (seperated by '|')
     * then in data packages (or key value pairs) (sperated by ' ')
     * if the datapackage is a key value pair it split this at '='
     * @param string $data
     * @return Array in form Array(0=>Array('key0'=>'val0','key1'=>'val1'), 1=>Array('key0'=>'val2','key1','val3'));
     */
    protected function parseData($data)
    {
        $parsed = Array();
        $items = \explode(self::SEPERATOR_ITEM, $data); //split up into single lists or blocks
        foreach ($items as $itemkey => $item)
        {
            $keyvals = explode(self::SEPERAOR_DATA, $item); //split up into data items or keyvalue pairs
            foreach ($keyvals as $keyval)
            {
                $keyval = explode(self::SEPERATOR_KEY_VAL, $keyval, 2); //parses key value pairs
                if (\trim($keyval[0]) === '')
                {
                    continue;
                }
                $keyval[1] = isset($keyval[1]) ? $keyval[1] : NULL;
                $parsed[$itemkey][$keyval[0]] = $this->parseValue($keyval[1]);
            }
        }
        return $parsed;
    }

    /**
     * Parses a value from the query
     * detects the following types:
     * int,boolean,null and string, where strings got unescaped
     * @param string $val
     * @return string|int|boolean|null 
     */
    protected function parseValue($val)
    {
        $val = \trim($val);
        if (ctype_digit($val))
        {
            return (int) $val;
        }
        if (\preg_match("/^true$/Di", $val) === 1)
        {
            return TRUE;
        }
        if (\preg_match("/^false$/Di", $val) === 1)
        {
            return FALSE;
        }
        if ($val === '' || $val === NULL)
        {
            return NULL;
        }


        return $this->unescape($val);
    }

    /**
     * Checks if the given string is a complete event.
     * because of actual restrictions of the query protocol this function only checks if the strin is nonempty
     * @param type $raw
     * @return type 
     */
    public function isCompleteEvent($raw)
    {
        if (\trim($raw) !== '')
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Checks if the given string is a complete response
     * The function is doing that by checking for a error section
     * @param string $raw
     * @return boolean 
     */
    public function isCompleteResponse($raw)
    {
        if (\preg_match("/error id=[0-9]* msg=/", $raw))
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Parses Events coming from the query
     * @param string $raw
     * @return \devmx\Teamspeak3\Query\Response 
     */
    public function getEventInstances($raw)
    {
        $events = \explode(self::SEPERATOR_RESPONSE, $raw);
        foreach ($events as $rawevent)
        {
            $ret[] = $this->parseEvent($rawevent);
        }
        return $ret;
    }

    /**
     * Returns the length of a normal welcomemessage
     * @return type 
     */
    public function getWelcomeMessageLength()
    {
        return self::WELCOME_LENGTH;
    }

    /**
     * Checks if the given string is a valid welcomemessage,
     * by checking length and for identifyer
     * @param string $welcome
     * @return boolean
     */
    public function isWelcomeMessage($welcome)
    {
        if (\strlen($welcome) !== self::WELCOME_LENGTH ||
                !\strstr($welcome, self::WELCOME_IDENTIFY))
        {
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }

}

?>
