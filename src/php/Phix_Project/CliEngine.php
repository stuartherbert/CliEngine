<?php

/**
 * Copyright (c) 2013-present Stuart Herbert.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of the
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package     Phix_Project
 * @subpackage  CliEngine
 * @author      Stuart Herbert <stuart@stuartherbert.com>
 * @copyright   2013-present Stuart Herbert. www.stuartherbert.com
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://www.phix-project.org
 * @version     @@PACKAGE_VERSION@@
 */

namespace Phix_Project;

use stdClass;

use Phix_Project\CliEngine\CliCommand;
use Phix_Project\CliEngine\CliEngineSwitch;
use Phix_Project\CliEngine\CliResult;
use Phix_Project\CliEngine\OutputWriter;

use Phix_Project\CommandLineLib4\CommandLineParser;
use Phix_Project\CommandLineLib4\DefinedSwitches;
use Phix_Project\CommandLineLib4\ParsedCommandLine;

/**
 * Main interface into Phix's CLI engine
 *
 * @package     Phix_Project
 * @subpackage  CliEngine
 * @author      Stuart Herbert <stuart@stuartherbert.com>
 * @copyright   2013-present Stuart Herbert. www.stuartherbert.com
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://www.phix-project.org
 * @version     @@PACKAGE_VERSION@@
 */
class CliEngine
{
	/**
	 * The name of the app that is using this engine
	 *
	 * @var string
	 */
	protected $appName;

	/**
	 * The version of the app that is using this engine
	 *
	 * @var string
	 */
	protected $appVersion;

	/**
	 * Who holds the copyright for this app
	 *
	 * @var string
	 */
	protected $appCopyright;

	/**
	 * The license that this app has been released under
	 *
	 * @var string
	 */
	protected $appLicense;

	/**
	 * The URL to go to learn more about the app
	 *
	 * @var string
	 */
	protected $appUrl;

	/**
	 * The default command that we execute if the user does not specify
	 * a command to run
	 *
	 * @var Phix_Project\CliEngine\CliCommand
	 */
	protected $defaultCommand;

	/**
	 * An array containing all of the commands that this engine knows
	 * about
	 *
	 * @var array
	 */
	protected $allCommands = array();

	/**
	 * An array containing all of the switches that this engine knows
	 * about
	 *
	 * @var array
	 */
	protected $engineSwitches = array();

	/**
	 * A list of all of the parser definitions for the switches that we
	 * accept
	 *
	 * @var DefinedSwitches
	 */
	protected $engineSwitchDefinitions;

	const PROCESS_COMPLETE = 1;
	const PROCESS_CONTINUE = 2;

	/**
	 * options set by the engine switches
	 *
	 * @var stdClass
	 */
	public $options;

	// ==================================================================
	//
	// constructor and internal initialisation
	//
	// ------------------------------------------------------------------

	public function __construct()
	{
		// we start with an empty list of switch definitions
		$this->engineSwitchDefinitions = new DefinedSwitches();

		// we start with an empty options sectoin
		$this->options = new stdClass();

		// create our output writer
		$this->output = new OutputWriter();
	}

	// ==================================================================
	//
	// API for setting up the engine
	//
	// ------------------------------------------------------------------

	public function addEngineSwitch(CliEngineSwitch $switch)
	{
		$definition = $switch->getDefinition();
		$this->engineSwitchDefinitions->addSwitch($definition);
		$this->engineSwitches[$definition->name] = $switch;
	}

	public function addCommand(CliCommand $command)
	{
		$this->allCommands[$command->getName()] = $command;
	}

	public function setAppName($name)
	{
		$this->appName = $name;
	}

	public function setAppVersion($version)
	{
		$this->appVersion = $version;
	}

	public function setAppCopyright($copyright)
	{
		$this->appCopyright = $copyright;
	}

	public function setAppLicense($license)
	{
		$this->appLicense = $license;
	}

	public function setAppUrl($url)
	{
		$this->appUrl = $url;
	}

	public function setDefaultCommand(CliCommand $command)
	{
		// add this command to the pile
		$this->addCommand($command);

		// remember that this is the default, when the user does not
		// specify a command to run
		$this->defaultCommand = $command;
	}

	// ==================================================================
	//
	// API for executing the engine
	//
	// ------------------------------------------------------------------

	public function main($argv)
	{
		// parse the switches before any command
		$parsed = $this->parseEngineSwitches($argv);
		if ($parsed === null)
		{
			// an error occurred
			return 1;
		}

		// now process the switches that we have
		$continue = $this->processEngineSwitches($parsed->switches);
		if ($continue->isComplete())
		{
			return $continue->returnCode;
		}

		// at this point, all of the active switches have done their
		// thing, and may have updated the contents of $this->options
		//
		// now we need to find our command to execute
		list($command, $cmdSwitches, $cmdArgs) = $this->findCommand($parsed->args);

		// if there are switches, they need processing
		if ($cmdSwitches !== null)
		{
			$continue = $command->processSwitches($this, $parsed->switches);
			if ($continue->isComplete())
			{
				// all done
				return $continue->returnCode;
			}
		}

		// now we are ready to execute the command
		$result = $command->processCommand($this, $cmdArgs);

		// all done
		return $result;
	}

	protected function parseEngineSwitches($argv)
	{
		// parse the switches before any command
		$parser = new CommandLineParser();
		$parsed = $parser->parseCommandLine($argv, 1, $this->engineSwitchDefinitions);

		// were there any errors?
		if (count($parsed->errors))
		{
			// yes - something went wrong
			foreach ($parsed->errors as $errorMsg)
			{
				$this->output->stderr->outputLine(
					$this->output->errorPrefix .
					$errorMsg . "\n"
				);
			}

			// that could have gone better
			return null;
		}

		// if we get here, all is well
		return $parsed;
	}

	protected function processEngineSwitches($parsedSwitches)
	{
		// execute each switch that has been used on the command line
		// (or that has a default value), in the order that they were
		// added to the engine
		foreach ($this->engineSwitches as $defName => $switch)
		{
			if (!isset($parsedSwitches[$defName]))
			{
				// nothing to see ... move along ... move along
				continue;
			}

			// shorthand
			$parsedSwitch = $parsedSwitches[$defName];

			// tell the switch to do its thing
			$continue = $switch->process(
				$this,
				$parsedSwitch->invokes,
				$parsedSwitch->values,
				$parsedSwitch->isUsingDefaultValue
			);

			// did we get a valid CliResult object back?
			if (! $continue instanceof CliResult)
			{
				var_dump($continue);
				// programming error
				die("Switch " . get_class($switch) . "::process() did not return a CliResult object\n");
			}

			// does this switch want everything to stop?
			if ($continue->isComplete())
			{
				// all done
				return $continue;
			}
		}

		// if we get here, all is well
		return new CliResult(CliResult::PROCESS_CONTINUE);
	}

	protected function findCommand($argv)
	{
		// do we *have* a command?
		if (count($argv) == 0)
		{
			// no - use the default command
			$command     = $this->defaultCommand;
			$cmdSwitches = null;
			$cmdArgs     = array();
		}
		else
		{
			// isolate the command name
			$commandName = $argv[0];

			// now, do we have this command?
			if (!isset($this->allCommands[$commandName]))
			{
				die("Unknown command '{$commandName}'");
			}

			// we have our command
			$command = $this->allCommands[$commandName];

			// we need to parse the remaining arguments for additional
			// switches
			$cmdSwitchDefs = $command->getSwitchDefinitions();
			if ($cmdSwitchDefs instanceof DefinedSwitches)
			{
				// parse the remaining command line
				$parser = new CommandLineParser();
				$parsed = $parser->parseCommandLine($argv, 1, $cmdSwitchDefs);

				// we have some switches to deal with later
				$cmdSwitches = $parsed->switches;

				// the remaining args are the args into the command
				$cmdArgs = $parsed->args;
			}
			else
			{
				// the command has no switches, with simplifies things
				// a lot
				$cmdSwitches = null;
				if (count($argv) > 1)
				{
					$cmdArgs = array_slice($argv, 1);
				}
				else
				{
					$cmdArgs = array();
				}
			}
		}

		// all done
		return array($command, $cmdSwitches, $cmdArgs);
	}

	// ==================================================================
	//
	// Getters
	//
	// ------------------------------------------------------------------

	public function getAppName()
	{
		return $this->appName;
	}

	public function getAppCopyright()
	{
		return $this->appCopyright;
	}

	public function getAppLicense()
	{
		return $this->appLicense;
	}

	public function getAppUrl()
	{
		return $this->appUrl;
	}

	public function getAppVersion()
	{
		return $this->appVersion;
	}

	public function getCommand($name)
	{
		if (isset($this->allCommands[$name]))
		{
			return $this->allCommands[$name];
		}

		return null;
	}

	public function getCommandsList()
	{
		return $this->allCommands;
	}

	public function getEngineSwitchDefinitions()
	{
		return $this->engineSwitchDefinitions;
	}
}