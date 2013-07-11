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

namespace Phix_Project\CliEngine;

use ReflectionClass;

use Phix_Project\CliEngine;
use Phix_Project\CliEngine\Helpers\HelpHelper;
use Phix_Project\CommandLineLib4\DefinedSwitches;

/**
 * Base class for all CLI commands
 *
 * @package     Phix_Project
 * @subpackage  CliEngine
 * @author      Stuart Herbert <stuart@stuartherbert.com>
 * @copyright   2013-present Stuart Herbert. www.stuartherbert.com
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://www.phix-project.org
 * @version     @@PACKAGE_VERSION@@
 */
abstract class CliCommand
{
	protected $name;

	protected $shortDescription = "no description set";
	protected $longDescription = "This command has provided no description of itself yet.\n";

	protected $argsList = array();

	// ==================================================================
	//
	// API for getting / setting
	//
	// ------------------------------------------------------------------

	public function getArgsList()
	{
		return $this->argsList;
	}

	public function setArgsList($list)
	{
		$this->argsList = $list;

		return $this;
	}

	/**
	 * get the long description of this command
	 *
	 * @return string
	 */
	public function getLongDescription()
	{
	    return $this->longDescription;
	}

	/**
	 * set the long description of this command
	 *
	 * @param string $description
	 *        the new long description of this command
	 */
	public function setLongDescription($description)
	{
	    $this->longDescription = $description;

	    return $this;
	}

	/**
	 * what is this command called?
	 *
	 * @return string
	 */
	public function getName()
	{
	    return $this->name;
	}

	/**
	 * tell this command what it is called
	 *
	 * @param string $name
	 */
	public function setName($name)
	{
	    $this->name = $name;

	    return $this;
	}

	/**
	 * get the short description of this command
	 *
	 * @return string
	 */
	public function getShortDescription()
	{
	    return $this->shortDescription;
	}

	/**
	 * set the short description of this command
	 *
	 * @param string $description
	 *        the new short description of this command
	 */
	public function setShortDescription($description)
	{
	    $this->shortDescription = $description;

	    return $this;
	}

	/**
	 * return a list of switches supported by this command
	 *
	 * @return DefinedSwitches
	 */
	public function getSwitchDefinitions()
	{
		return null;
	}

	// ==================================================================
	//
	// API for handling switches that this command supports
	//
	// ------------------------------------------------------------------

	/**
	 * process any switches we've been given on the command-line for
	 * THIS specific command
     *
     * @param  CliEngine $engine
     *         the CliEngine object
	 * @param  array $switches
	 *         a list of switches to process
     * @param  mixed $additionalContext
     *         additional data injected by the caller to CliEngine::main()
	 *
	 * @return Phix_Project\CliEngine\CliResult
	 */
	public function processSwitches(CliEngine $engine, $switches = array(), $additionalContext = null)
	{
		// do nothing
		return new CliResult(CliResult::PROCESS_CONTINUE);
	}

	// ==================================================================
	//
	// API for the command to do its thing
	//
	// ------------------------------------------------------------------

	/**
	 * do the actual command
	 *
	 * @param  CliEngine $engine
	 *         the tool that is running
	 * @param  array $params
	 *         a list of parameters (if any) that have been passed to us
     * @param  mixed $additionalContext
     *         additional data injected by the caller to CliEngine::main()
	 * @return integer
	 *         the value to return to the process or shell that called
	 *         this tool
	 *
	 * 		   should be '0' for success, and >0 for an error
	 */
	abstract public function processCommand(CliEngine $engine, $params = array(), $additionalContext = null);

	// ==================================================================
	//
	// API for self-describing commands
	//
	// ------------------------------------------------------------------

    public function outputHelp(CliEngine $engine)
    {
        // shorthand
        $op = $engine->output;
        $so = $engine->output->stdout;
    	$hh = new HelpHelper();

        $options = $this->getSwitchDefinitions();
        $args    = $this->getArgsList();

        $sortedSwitches = null;
        if ($options !== null)
        {
            $sortedSwitches = $options->getSwitchesInDisplayOrder();
        }

        $this->showName($op, $so, $hh, $engine->getAppName());
        $this->showSynopsis($op, $so, $hh, $engine->getAppName(), $sortedSwitches, $args);
        $this->showOptions($op, $so, $hh, $sortedSwitches, $args);
        $this->showLongDescription($op, $so, $hh);
        $this->showImplementationDetails($op, $so, $hh);
    }

    protected function showName($op, $so, $hh, $appName)
    {
        $so->setIndent(0);
        $so->outputLine(null, 'NAME');
        $so->setIndent(4);
        $so->output($op->commandStyle, $appName . ' ' . $this->getName());
        $so->outputLine(null, ' - ' . $this->getShortDescription());
        $so->addIndent(-4);
        $so->outputBlankLine();
    }

    protected function showSynopsis($op, $so, $hh, $appName, $sortedSwitches, $args)
    {
        $so->setIndent(0);
        $so->outputLine(null, 'SYNOPSIS');
        $so->setIndent(4);

        $so->output($op->commandStyle, $appName . ' ' . $this->getName());

        if ($sortedSwitches !== null)
        {
            $this->showSwitchSummary($op, $so, $hh, $sortedSwitches);
        }

        if (count($args) > 0)
        {
            $this->showArgsSummary($op, $so, $hh, $args);
        }

        $so->outputBlankLine();
    }

    protected function showArgsSummary($op, $so, $hh, $args)
    {
        foreach ($args as $arg => $argDesc)
        {
            $so->output($op->argStyle, ' ' . $arg);
        }
    }

    protected function showSwitchSummary($op, $so, $hh, $sortedSwitches)
    {
    	$hh->showSwitchSummary($op, $so, $sortedSwitches);
    }

    protected function showOptions($op, $so, $hh, $sortedSwitches, $args)
    {
        // do we have any options to show?
        if (empty($sortedSwitches['allSwitches']) && empty($args))
        {
            // no we do not
            return;
        }

        $so = $op->stdout;

        $so->setIndent(0);
        $so->outputLine(null, 'OPTIONS');
        $so->addIndent(4);

        if (count($sortedSwitches['allSwitches']) > 0)
        {
            $this->showSwitchDetails($op, $so, $hh, $sortedSwitches);
        }

        if (count($args) > 0)
        {
            $this->showArgsDetails($op, $so, $hh, $args);
        }

        $so->addIndent(-4);
    }

    protected function showSwitchDetails($op, $so, $hh, $sortedSwitches)
    {
        // keep track of the switches we have seen, to avoid
        // any duplication of output
        $seenSwitches = array();

        foreach ($sortedSwitches['allSwitches'] as $shortOrLongSwitch => $switch)
        {
           // have we already seen this switch?
	        if (isset($seenSwitches[$switch->name]))
            {
                // yes, skip it
                continue;
            }
            $seenSwitches[$switch->name] = $switch;

            // we have not seen this switch before
            $hh->showSwitchLongDetails($op, $so, $switch);
        }
    }

    protected function showArgsDetails($op, $so, $hh, $args)
    {
        foreach ($args as $argName => $argDesc)
        {
            $this->showArgLongDetails($op, $so, $hh, $argName, $argDesc);
        }
    }

    protected function showArgLongDetails($op, $so, $hh, $argName, $argDesc)
    {
        $so->outputLine($op->argStyle, $argName);
        $so->addIndent(4);
        $so->outputLine(null, $argDesc);
        $so->addIndent(-4);
        $so->outputBlankLine();
    }

    protected function showLongDescription($op, $so, $hh)
    {
        $so->setIndent(0);
        $so->outputLine(null, 'DESCRIPTION');
        $so->setIndent(4);
        $so->outputLine($this->getLongDescription());
    }

    protected function showImplementationDetails($op, $so, $hh)
    {
        $so->setIndent(0);
        $so->outputLine(null, 'IMPLEMENTATION');
        $so->addIndent(4);
        $so->outputLine(null, 'This command is implemented in the PHP class:');
        $so->outputBlankLine();
        $so->output($op->commandStyle, '* ');
        $so->addIndent(2);
        $so->outputLine(null, get_class($this));
        $so->addIndent(-2);
        $so->outputBlankLine();
        $so->outputLine(null, 'which is defined in the file:');
        $so->outputBlankLine();
        $so->output($op->commandStyle, '* ');
        $so->addIndent(2);
        $so->outputLine(null, $this->getSourceFilename());
        $so->addIndent(-6);
    }

    protected function getSourceFilename()
    {
    	return __FILE__;
    }
}