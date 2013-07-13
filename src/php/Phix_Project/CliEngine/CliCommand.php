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

use ReflectionObject;
use stdClass;

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

    protected $definedSwitches = null;
    protected $switches = array();

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
	 * @return DefinedSwitches
	 */
	public function getSwitchDefinitions()
	{
        if ($this->definedSwitches == null) {
            $this->definedSwitches = new DefinedSwitches();
        }

        return $this->definedSwitches;
	}

    public function getSwitchesList()
    {
        return $this->switches;
    }

    /**
     * provide the list of switches that this command supports
     *
     * @param array(CliCommandSwitch) $switches
     */
    public function setSwitches($switches)
    {
        if (!$this->definedSwitches) {
            $this->definedSwitches = new DefinedSwitches();
        }

        foreach ($switches as $switch) {
            $this->definedSwitches->addSwitch($switch);
            $this->switches[$switch->name] = $switch;
        }
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
        $s = new stdClass;
        $s->op = $engine->output;
        $s->so = $engine->output->stdout;
    	$s->hh = new HelpHelper();
        $s->appName = $engine->getAppName();
        $s->myName = $this->getName();
        $s->isDefaultCommand = false;
        if ($engine->getDefaultCommand() === $this)
        {
            $s->isDefaultCommand = true;
        }

        $engineSwitchDefinitions = $engine->getSwitchDefinitions();
        $engineSortedSwitches = null;
        if ($engineSwitchDefinitions !== null)
        {
            $s->engineSortedSwitches = $engineSwitchDefinitions->getSwitchesInDisplayOrder(array('actsAsCommand' => false));
        }

        $mySwitchDefintions = $this->getSwitchDefinitions();
        $mySortedSwitches = null;
        if ($mySwitchDefintions !== null)
        {
            $s->mySortedSwitches = $mySwitchDefintions->getSwitchesInDisplayOrder();
        }

        $s->myArgs = $this->getArgsList();

        // this is the order we display things in
        $this->showName($s);
        $this->showSynopsis($s);
        $this->showLongDescription($s);
        $this->showOptions($s);
        $this->showImplementationDetails($s);
    }

    protected function showName($s)
    {
        $s->so->setIndent(0);
        $s->so->outputLine('NAME');
        $s->so->setIndent(4);
        $s->so->output($s->op->commandStyle, $s->appName . ' ' . $s->myName);
        $s->so->outputLine(' - ' . $this->getShortDescription());
        $s->so->addIndent(-4);
        $s->so->outputBlankLine();
    }

    protected function showSynopsis($s)
    {
        $s->so->setIndent(0);
        $s->so->outputLine('SYNOPSIS');
        $s->so->setIndent(4);

        $s->so->output($s->op->commandStyle, $s->appName);
        if ($s->engineSortedSwitches !== null) {
            $this->showSwitchSummary($s, $s->engineSortedSwitches);
            $s->so->output(' ');
        }

        $s->so->output($s->op->commandStyle, $s->myName);

        if ($s->mySortedSwitches !== null)
        {
            $this->showSwitchSummary($s, $s->mySortedSwitches);
        }

        if (count($s->myArgs) > 0)
        {
            $this->showArgsSummary($s);
        }

        $s->so->outputBlankLine();

        // is this the default command for the engine?
        if (!$s->isDefaultCommand)
        {
            // no, so we're done here
            return;
        }

        // if we get here, then this is the default command for the app,
        // and we need to tell the user about this
        $s->so->output("This is the default command for ");
        $s->so->output($s->op->commandStyle, $s->appName);
        $s->so->output("; you can omit ");
        $s->so->output($s->op->commandStyle, $s->myName);
        $s->so->outputLine(" from the command line like this:");
        $s->so->outputBlankLine();

        $s->so->output($s->op->commandStyle, $s->appName);
        if ($s->engineSortedSwitches !== null) {
            $this->showSwitchSummary($s, $s->engineSortedSwitches);
        }

        if ($s->mySortedSwitches !== null)
        {
            $this->showSwitchSummary($s, $s->mySortedSwitches);
        }

        if (count($s->myArgs) > 0)
        {
            $this->showArgsSummary($s);
        }

        $s->so->outputBlankLine();
    }

    protected function showArgsSummary($s)
    {
        foreach ($s->myArgs as $arg => $argDesc)
        {
            $s->so->output($s->op->argStyle, ' ' . $arg);
        }
    }

    protected function showSwitchSummary($s, $sortedSwitches)
    {
    	$s->hh->showSwitchSummary($s->op, $s->so, $sortedSwitches);
    }

    protected function showOptions($s)
    {
        // do we have any options to show?
        if (empty($s->engineSortedSwitches) && empty($s->mySortedSwitches['allSwitches']) && empty($s->myArgs))
        {
            // no we do not
            return;
        }

        $s->so->setIndent(0);
        $s->so->outputLine('OPTIONS');
        $s->so->addIndent(4);

        if (count($s->mySortedSwitches['allSwitches']) > 0)
        {
            $this->showSwitchDetails($s, $s->mySortedSwitches);
        }

        if (count($s->myArgs) > 0)
        {
            $this->showArgsDetails($s);
        }

        if (count($s->engineSortedSwitches['allSwitches']) > 0)
        {
            $s->so->setIndent(0);
            $s->so->outputLine('GLOBAL OPTIONS');
            $s->so->addIndent(4);
            $s->so->output("All ");
            $s->so->output($s->op->commandStyle, $s->appName);
            $s->so->outputLine(" commands also support the following options. They are normally placed in front of the command on the command line.");
            $s->so->outputBlankLine();
            $this->showSwitchDetails($s, $s->engineSortedSwitches);
        }


        $s->so->addIndent(-4);
    }

    protected function showSwitchDetails($s, $sortedSwitches)
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

            // we have not seen this switch before
            $seenSwitches[$switch->name] = $switch;

            // output the details about this switch
            $s->hh->showSwitchLongDetails($s->op, $s->so, $switch);
        }
    }

    protected function showArgsDetails($s)
    {
        foreach ($s->myArgs as $argName => $argDesc)
        {
            $this->showArgLongDetails($s, $argName, $argDesc);
        }
    }

    protected function showArgLongDetails($s, $argName, $argDesc)
    {
        $s->so->outputLine($s->op->argStyle, $argName);
        $s->so->addIndent(4);
        $s->so->outputLine($argDesc);
        $s->so->addIndent(-4);
        $s->so->outputBlankLine();
    }

    protected function showLongDescription($s)
    {
        $s->so->setIndent(0);
        $s->so->outputLine('DESCRIPTION');
        $s->so->setIndent(4);
        $s->so->outputLine($this->getLongDescription());
    }

    protected function showImplementationDetails($s)
    {
        $s->so->setIndent(0);
        $s->so->outputLine('IMPLEMENTATION');
        $s->so->addIndent(4);
        $s->so->outputLine('This command is implemented in the PHP class:');
        $s->so->outputBlankLine();
        $s->so->output($s->op->commandStyle, '* ');
        $s->so->addIndent(2);
        $s->so->outputLine(get_class($this));
        $s->so->addIndent(-2);
        $s->so->outputBlankLine();
        $s->so->outputLine('which is defined in the file:');
        $s->so->outputBlankLine();
        $s->so->output($s->op->commandStyle, '* ');
        $s->so->addIndent(2);
        $s->so->outputLine(null, $this->getSourceFilename());
        $s->so->addIndent(-6);
    }

    protected function getSourceFilename()
    {
        $refObj = new ReflectionObject($this);
    	return $refObj->getFileName();
    }
}