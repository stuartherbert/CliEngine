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

use Phix_Project\CliEngine\CliCommand;
use Phix_Project\CliEngine\CliEngineSwitch;
use Phix_Project\CliEngine\OutputWriter;

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
	 * The copyright that the app is released under
	 *
	 * @var string
	 */
	protected $appCopyright;

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

	// ==================================================================
	//
	// API for setting up the engine
	//
	// ------------------------------------------------------------------

	public function addEngineSwitch(CliEngineSwitch $switch)
	{
		// $this->engineSwitches[$switch->longname] = $switch;
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
		// create our output writer
		$this->output = new OutputWriter();

		// parse the switches before any command

		// find the command

		// parse any switches for that command

		// execute the command
	}
}