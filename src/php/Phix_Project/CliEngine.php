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

	/**
	 * the defaults set by the caller
	 *
	 * @var array
	 */
	public $defaults;

	// ==================================================================
	//
	// constructor and internal initialisation
	//
	// ------------------------------------------------------------------

	public function __construct($defaults = null)
	{
		// we start with an empty list of switch definitions
		$this->engineSwitchDefinitions = new DefinedSwitches();

		// we start with an empty options sectoin
		$this->options = new stdClass();

		// create our output writer
		$this->output = new OutputWriter();

		if ($defaults === null) {
			$this->defaults = array();
		}
		else {
			$this->defaults = $defaults;
		}
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

	public function main($argvList, $additionalContext)
	{
		// before we do anything else, we need to merge the defaults in
		// with the command-line that we have been given
		//
		// this can have any combination of switches and command, just
		// like a real argv
		//
		// as there are a lot of different scenarios to handle, it's best
		// that mergeDefaultsIntoArgv() returns us the processed results,
		// rather than a command-line that we have to parse yet again
		list($command, $switches, $parsed) = $this->mergeDefaultsIntoArgv($argvList);

		// var_dump($argv);
		// exit(0);

		// is there a command?
		if ($command === null) {
			// let's tell the user how to use us
			$hh = new HelpHelper();
			$hh->showShortHelp($this);
			return 1;
		}

		// did we successfully parse the switches?
		if ($parsed === null) {
			// an error occurred
			return 1;
		}

		// now process the switches that we have
		$continue = $this->processSwitches($switches, $parsed->switches, $additionalContext);
		if ($continue->isComplete())
		{
			return $continue->returnCode;
		}

		// whatever is left becomes the parameters to the command
		//
		// now we are ready to execute the command
		$result = $command->processCommand($this, $parsed->args, $additionalContext);

		// all done
		return $result;
	}

	protected function mergeDefaultsIntoArgv($argv)
	{
		// zero, one or both of these lists may have commands in
		//
		// if so, the command in $argv takes precedence
		$argvCount          = count($argv);
		$argvHasCommand     = false;
		$defaultsCount      = count($this->defaults);
		$defaultsHasCommand = false;

		// special case - no defaults
		if ($defaultsCount == 0) {
			list($argvCommand, $commandArgvIndex) = $this->determineCommand($argv, 1);
			if ($argvCommand === null) {
				// something went wrong
				return array(null, null, null);
			}
			$switches = $this->buildSwitchListFor($argvCommand);
			$parsed   = $this->parseSwitches($argvCommand, $argv, 1, $switches);
			return array($argvCommand, $switches, $parsed);
		}

		// special case - nothing on the command-line
		if ($argvCount == 1) {
			list($defaultsCommand, $commandDefaultsIndex) = $this->determineCommand($this->defaults, 0);
			if ($defaultsCommand === null) {
				// something went wrong
				return array(null, null, null);
			}
			$switches = $this->buildSwitchListFor($defaultsCommand);
			$parsed   = $this->parseSwitches($defaultsCommand, $this->defaults, 0, $switches);
			return array($defaultsCommand, $switches, $parsed);
		}

		// at this point, the user has given us both:
		//
		// 1. some defaults from their config file, and
		// 2. something on the command-line
		//
		// what is the user trying to do on the command-line? Here are the
		// scenarios that we currently support
		//
		// 1a. use extra flags to tailor the default behaviour
		//
		//    in this scenario, the user wants the default behaviour, and
		//    their flags applied too
		//
		//    we will add their flags to the command line before we parse
		//    and process it
		//
		// 1b. change the settings for flags in the defaults list
		//
		//    in this scenario, the user wants to override a flag that
		//    has been set in the list of defaults
		//
		//    this is currently really tricky to support well
		//
		// 1c. use alternative command args
		//
		//    in this scenario, the user wants the flags from the default
		//    behaviour, they just don't want the default args
		//
		// 2. use alternative command
		//
		//    in this scenario, the user wants to ignore the defaults
		//    completely
		//
		// any combination of scenario 1a, 1b and 1c are valid, and we need
		// to support them well
		//
		// there's a lot of complexity here, and it's impossible to be sure
		// that a user won't have an unsupported scenario in mind when they
		// come to use the software :(

		// do we have a command in the defaults list?
		list($defaultsCommand, $commandDefaultsIndex) = $this->determineCommand($this->defaults, 0);
		if ($defaultsCommand !== null && $commandDefaultsIndex !== null) {
			$defaultsHasCommand = true;
		}

		// do we have a command in the argv list?
		list($argvCommand, $commandArgvIndex) = $this->determineCommand($argv, 1);
		if ($argvCommand !== null && $commandArgvIndex !== null) {
			$argvHasCommand = true;
		}

		// special case - no command found
		if ($defaultsCommand === null && $argvCommand === null) {
			return array(null, null, null);
		}

		// are they the same command?
		if ($argvCommand !== $defaultsCommand) {
			// no - so this is scenario 2
			//
			// we abandon the defaults, and let the command-line take charge
			var_dump("scenario 2");
			$switches = $this->buildSwitchListFor($argvCommand);
			$parsed   = $this->parseSwitches($argvCommand, $argv, 1, $switches);
			return array($argvCommand, $switches, $parsed);
		}

		// at this point, we are in scenario 1
		//
		// we don't yet know which part(s) of scenario 1 we are facing
		//
		// let's start by understanding both command-lines
		$switches = $this->buildSwitchListFor($argvCommand);
		$parsedDefaults = $this->parseSwitches($argvCommand, $this->defaults, 0, $switches);
		$parsedArgv     = $this->parseSwitches($argvCommand, $argv, 1, $switches);
		if ($parsedDefaults === null || $parsedArgv === null) {
			// an error occurred
			return array(null, null, null);
		}

		// at this point, we have parsed both command-lines, and now need
		// to selectively merge them into one final, returnable list
		//
		// this is crude, but it should do the trick
		foreach ($parsedArgv->switches as $key => $parsedSwitch) {
			// do not let a switch's default value override anything
			// that has been set in the config file
			if ($parsedSwitch->testIsDefaultValue() && isset($parsedDefaults->switches[$key])) {
				continue;
			}

			// if we get here, override the switch in the config file!
			$parsedDefaults->switches[$key] = $parsedSwitch;
		}

		// don't forget to override command-line args if needed
		if (count($parsedArgv->args) > 0) {
			$parsedDefaults->args = $parsedArgv->args;
		}

		// all done
		return array($argvCommand, $switches, $parsedDefaults);
	}

	protected function determineCommand($argv, $argMin = 0)
	{
		// we're looking for the command that we're parsing for, so that
		// we know what switches to parse for

		// a list of the commands that we have found
		$commands = [];

		// start at the right-hand side, and work left until we find
		// a recognised command
		$argMax   = count($argv);
		for ($argIndex = $argMin; $argIndex < $argMax; $argIndex++)
		{
			$command = $this->getCommand($argv[$argIndex]);
			if ($command !== null) {
				// we have a command
				return array($command, $argIndex);
			}
		}

		// special case .. implicit command with no params or switches
		if ($this->hasDefaultCommand()) {
			return array($this->getDefaultCommand(), null);
		}

		// error - explicit command required, but the command line
		// has nothing at all on it
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

	protected function parseSwitches($command, $argv, $argIndex, DefinedSwitches $definedSwitches)
	{
		// create the parser to parse the command line
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

		// at this point, we need to remove the command's name from
		// the arguments list
		if (isset($parsed->args[0]) && $parsed->args[0] == $command->getName()) {
			$parsed->args = array_slice($parsed->args, 1);
		}

		// all done
		return $parsed;
	}

	protected function processSwitches(DefinedSwitches $definedSwitches, $parsedSwitches, $additionalContext = null)
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
				$parsedSwitch->isUsingDefaultValue,
				$additionalContext
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