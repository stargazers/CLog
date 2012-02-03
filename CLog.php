<?php

/* 
CLog - Class for logging.
Copyright (C) 2011-2012 Aleksi Räsänen <aleksi.rasanen@runosydan.net>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

	// ************************************************** 
	//  CLog
	/*!
		@brief Class for logging
		@author Aleksi Räsänen
		@email aleksi.rasanen@runosydan.net
		@license GNU AGPL
		@copyright Aleksi Räsänen, 2011-2012
	*/
	// ************************************************** 
	class CLog
	{
		private $addTimeStamp;
		private $timeAndMessageSeparator;
		private $dateFormat;
		private $logMessages;
		private $addLineNumbers;
		private $messageTypes;
		private $showTypeInMessage;
		private $logFileWriteMethod;

		// ************************************************** 
		//  __construct
		/*!
			@brief Initialize class variables
		*/
		// ************************************************** 
		public function __construct()
		{
			$this->addTimeStamp = true;
			$this->timeAndMessageSeparator = '  ---  ';
			$this->dateFormat = 'Y-m-d H:i:s';
			$this->logMessages = array();
			$this->messageTypes = array();
			$this->addLineNumbers = false;
			$this->showTypeInMessage = false;
			
			$this->setLogFileWriteMethod( 'write' );
		}

		// ************************************************** 
		//  setLogFileWriteMethod
		/*!
			@brief Defines a log writing method when we want
			  to write a log to the file.
			@param $type Type of opening a file. This can be
			  'write' or 'append'. If other $type is given,
			  we use 'write' which is also a default value.
		*/
		// ************************************************** 
		public function setLogFileWriteMethod( $type )
		{
			switch( $type )
			{
				case 'append':
					$this->logFileWriteMethod = 'a';
					break;

				default:
				case 'write':
					$this->logFileWriteMethod = 'w';
					break;
			}
		}

		// ************************************************** 
		//  addLineNumbers
		/*!
			@brief Sets if we want to add line numbers to
			  log messages or not.
			@param $bool Boolean value. If true, we add line
			  numbers to textual output. If false we do not
			  add any line numbers.
		*/
		// ************************************************** 
		public function addLineNumbers( $bool )
		{
			$this->addLineNumbers = $bool;
		}

		// ************************************************** 
		//  showTypeInMessage
		/*!
			@brief Defines if we should show defined types
			  in messages. For example, if we have defined regexp
			  like '^In method: ' and we define showTypeInMessage
			  to false, we do not echo text "In method" to those
			  messages which are that type.
			@param $bool True or false. By default it is false.
		*/
		// ************************************************** 
		public function showTypeInMessage( $bool )
		{
			$this->showTypeInMessage = $bool;
		}

		// ************************************************** 
		//  writeLogToFile
		/*!
			@brief Write generated log to file
			@param $filename File where we write log file
			@param $format Format of log file. This can be
			  'text' or 'html' (not raw!).
			@return True if we could write log file, false if not.
		*/
		// ************************************************** 
		public function writeLogToFile( $filename, $format )
		{
			$fh = @fopen( $filename, $this->logFileWriteMethod );

			if(! $fh )
				return false;

			if( $format == 'raw' )
				$format = 'text';

			@fwrite( $fh, $this->getLog( $format ) );
			@fclose( $fh );

			return true;
		}

		// ************************************************** 
		//  addTimeStampsToLog
		/*!
			@brief Sets if we should add timestamps to log or not
			@param $bool Boolean value. True if we want to add
			  timestamps to log file, false if not.
		*/
		// ************************************************** 
		public function addTimeStampsToLog( $bool )
		{
			$this->addTimeStamp = $bool;
		}

		// ************************************************** 
		//  setDateFormat
		/*!
			@brief Set date formatting string
			@param $format Formatting string. Must be valid for
			  PHP's date function.
		*/
		// ************************************************** 
		public function setDateFormat( $format )
		{
			$this->dateFormat = $format;
		}

		// ************************************************** 
		//  setTimeAndMessageSeparator
		/*!
			@brief Set a separator string what will be places
			  between the timestamp and actual log message
			  if logging time is enabled
			@param $separator Separator string
		*/
		// ************************************************** 
		public function setTimeAndMessageSeparator( $separator )
		{
			$this->timeAndMessageSeparator = $separator;
		}

		// ************************************************** 
		//  add
		/*!
			@brief Add a message to log
			@param $msg Message
		*/
		// ************************************************** 
		public function add( $msg )
		{
			$this->logMessages[] = array(
				'timestamp' => time(),
				'message' => $msg
			);
		}

		// ************************************************** 
		//  getLog
		/*!
			@brief Get generated log messages
			@param $format Format what we use for return value.
			  This can be 'raw', 'text' or 'html'.
			@return Log in wanted format
		*/
		// ************************************************** 
		public function getLog( $format )
		{
			$log = '';
			$possible = array( 'raw', 'text', 'html' );

			if(! in_array( $format, $possible ) )
				$format = 'raw';

			if( $format == 'text' )
				$log = $this->formatLogToText();
			else if( $format == 'html' )
				$log = $this->formatLogToHTML();
			else
				$log = $this->logMessages;
			
			return $log;
		}

		// ************************************************** 
		//  clearLog
		/*!
			@brief Clears a log
		*/
		// ************************************************** 
		public function clearLog()
		{
			$this->logMessages = array();
		}

		// ************************************************** 
		//  setMessageType
		/*!
			@brief Set different message types with regular expressions
			@param $type Whatever name, for example "info", "error" etc.
			@param $regexp Regular expression how these files should
			  be found.
		*/
		// ************************************************** 
		public function setMessageType( $type, $regexp )
		{
			$this->messageTypes[$type] = $regexp;
		}

		// ************************************************** 
		//  formatLogToText
		/*!
			@brief Format log data to text
			@return String
		*/
		// ************************************************** 
		private function formatLogToText()
		{
			$str = '';

			foreach( $this->logMessages as $lineNo => $logRow )
			{
				if( $this->addLineNumbers )
				{
					$str .= str_pad( $lineNo + 1, 5, ' ', STR_PAD_LEFT );
					$str .= ': ';
				}

				if( $this->addTimeStamp )
				{
					$str .= date( $this->dateFormat, $logRow['timestamp'] );
					$str .= $this->timeAndMessageSeparator;
				}

				$str .= $logRow['message']. "\n";
			}

			return $str;
		}

		// ************************************************** 
		//  formatLogToHTML
		/*!
			@brief Format log data to HTML
			@return HTML String
		*/
		// ************************************************** 
		private function formatLogToHTML()
		{
			$str = '<table>' . "\n";

			foreach( $this->logMessages as $logRow )
			{
				$str .= '<tr>';

				if( $this->addTimeStamp )
				{
					$str .= '<td class="log_timestamp">';
					$str .= date( $this->dateFormat, $logRow['timestamp'] );
					$str .= '</td>';
				}

				$msg = $logRow['message'];
				$type = $this->checkMessageTypes( $msg );

				if( $type != '' )
				{
					$msg = $this->removeMessageTypeIfNeeded( $msg, $type );
					$type = '_' . $type;
				}

				$str .= '<td class="log_message' . $type . '">';
				$str .= $msg;
				$str .= '</td>';
				$str .= '</tr>' . "\n";
			}

			$str .= '</table>' . "\n";
			return $str;
		}

		// ************************************************** 
		//  removeMessageTypeIfNeeded
		/*!
			@brief Removes a message type text from message if
			  variable $showTypeInMessage is false.
			@param $message Message to handle
			@param $type Message type 
			@return String
		*/
		// ************************************************** 
		private function removeMessageTypeIfNeeded( $message, $type )
		{
			if( $this->showTypeInMessage )
				return $message;
			
			$whatToRemove = $this->messageTypes[$type];
			$whatToRemove = '/' . $whatToRemove . '/';

			$message = preg_replace( $whatToRemove, '', $message );
			return $message;
		}

		// ************************************************** 
		//  checkMessageTypes
		/*!
			@brief Checks message type from messageTypes array
			@param $message Message to check
			@return If any regexp in $messageTypes matches,
			  returns the first one which matches. If no matches
			  are found, returns empty string.
		*/
		// ************************************************** 
		private function checkMessageTypes( $message )
		{
			foreach( $this->messageTypes as $type => $regexp )
			{
				$regexp = '/' . $regexp . '/';
				preg_match( $regexp, $message, $matches );
				
				if( is_array( $matches ) && ! empty( $matches ) )
					return $type;
			}

			return '';
		}
	}
 
?>
