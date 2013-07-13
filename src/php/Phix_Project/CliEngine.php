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
use Phix_Project\CliEngine\CliSwitch;
use Phix_Project\CliEngine\CliResult;
use Phix_Project\CliEngine\OutputWriter;
use Phix_Project\CliEngine\Helpers\HelpHelper;

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

	public function addEngineSwitch(CliSwitch $switch)
	{
		$this->engineSwitches[$switch->name] = $switch;
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

	public function main($argv, $additionalContext)
	{
		// to parse the command line successfully, we need to know what
		// command we are parsing for, so that we can tell the parser
		// the correct set of switches to accept
		list($command, $commandArgvIndex) = $this->determineCommand($argv);

		// is there a command?
		if ($command === null) {
			// let's tell the user how to use us
			$hh = new HelpHelper();
			$hh->showShortHelp($this);
			return 1;
		}

		// what happens next all depends on whether the command is
		// implicit or explicit
		//
		// if the command is implicit, then engine switches and command
		// switches could appear together on the command line
		//
		// if the command is explicit, then the two sets of switches
		// cannot appear together on the command line


		// special case - implicit command
		if ($commandArgvIndex === null) {
			// implicit command
			//
			// expect both engine and command switches together
			$mergedSwitches = $this->buildSwitchListFor($command);
			$parsed = $this->parseSwitches($argv, 1, $mergedSwitches);
			if ($parsed === null) {
				// an error occurred
				return 1;
			}

			// now process the switches that we have
			$continue = $this->processSwitches($mergedSwitches, $parsed->switches);
			if ($continue->isComplete())
			{
				return $continue->returnCode;
			}
		}
		else {
			// explicit command
			//
			// parse the engine switches first
			$engineSwitches = $this->buildSwitchListFor(null);
			$parsed = $this->parseSwitches(array_slice($argv, 0, $commandArgvIndex - 1), 1, $engineSwitches);
			if ($parsed === null) {
				// an error occurred
				return 1;
			}
			// now process the switches that we have
			$continue = $this->processSwitches($engineSwitches, $parsed->switches);
			if ($continue->isComplete())
			{
				return $continue->returnCode;
			}

			// now, parse after the command
			$commandSwitches = $command->getSwitchDefinitions();
			$parsed = $this->parseSwitches(array_slice($argv, $commandArgvIndex), 1, $commandSwitches);
			if ($parsed === null) {
				// an error occurred
				return 1;
			}

			// now process the switches that we have
			$continue = $this->processSwitches($commandSwitches, $parsed->switches);
			if ($continue->isComplete())
			{
				return $continue->returnCode;
			}
		}

		// whatever is left becomes the parameters to the command
		$cmdArgs = $parsed->args;

		// now we are ready to execute the command
		$result = $command->processCommand($this, $cmdArgs, $additionalContext);

		// all done
		return $result;
	}

	protected function determineCommand($argv)
	{
		// we're looking for the command that we're parsing for, so that
		// we know what switches to parse for

		// skip over (potentially) global switches
		$argIndex = 1;
		$argMax   = count($argv);
		while ($argIndex < $argMax && $argv[$argIndex]{0} == '-') {
			$argIndex++;
		}

		if (!isset($argv[$argIndex])) {
			// special case .. implicit command with no params or switches
			if ($this->hasDefaultCommand()) {
				return array($this->getDefaultCommand(), null);
			}

			// error - explicit command required, but the command line
			// has nothing at all on it
			return array(null, null);
		}

		// do we have a command?
		$command = $this->getCommand($argv[$argIndex]);
		if ($command !== null) {
			return array($command, $argIndex);
		}

		// the command might be implicit
		if ($this->hasDefaultCommand()) {
			return array($this->getDefaultCommand(), null);
		}

		// we needed an explicit command, but we didn't get one
		return array(null, null);
	}

	protected function buildSwitchListFor($command)
	{
		$definedSwitches  = new DefinedSwitches();

		// add in the engine switches
		foreach ($this->engineSwitches as $switchName => $switch) {
			$definedSwitches->addSwitch($switch);
		}

		// is there a command?
		if ($command === null) {
			// no
			return $definedSwitches;
		}

		// now, add in the switches from the command
		$switches = $command->getSwitchDefinitions();
		foreach ($switches->getSwitches() as $switchName => $switch) {
			$definedSwitches->addSwitch($switch);
		}

		// all done
		return $definedSwitches;
	}

	protected function parseSwitches($argv, $argIndex, DefinedSwitches $definedSwitches)
	{
		// create the parser to parse the commadn line
		$parser = new CommandLineParser();

		// parse the command line
		$parsed = $parser->parseCommandLine($argv, $argIndex, $definedSwitches);

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

	protected function processSwitches(DefinedSwitches $definedSwitches, $parsedSwitches)
	{
		// execute each switch that has been used on the command line
		// (or that has a default value), in the order that they were
		// added to the engine
		foreach ($definedSwitches->getSwitches() as $defName => $switch)
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

	public function getSwitchDefinitions()
	{
		return $this->buildSwitchListFor(null);
	}

	public function hasDefaultCommand()
	{
		if ($this->defaultCommand) {
			return true;
		}

		return false;
	}

	public function getDefaultCommand()
	{
		return $this->defaultCommand;
	}
}