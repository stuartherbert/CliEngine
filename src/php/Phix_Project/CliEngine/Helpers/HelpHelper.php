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

namespace Phix_Project\CliEngine\Helpers;

use Phix_Project\CliEngine;
use Phix_Project\CommandLineLib4\DefinedSwitch;

/**
 * An assistant to display useful information about all of the commands
 * that a CliEngine knows about
 *
 * Used by:
 * * ShortHelpSwitch
 * * LongHelpSwitch
 * * HelpCommand
 *
 * @package     Phix_Project
 * @subpackage  CliEngine
 * @author      Stuart Herbert <stuart@stuartherbert.com>
 * @copyright   2013-present Stuart Herbert. www.stuartherbert.com
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://www.phix-project.org
 * @version     @@PACKAGE_VERSION@@
 */
class HelpHelper
{
    public function showShortHelp(CliEngine $engine)
    {
        // get the list of switches in display order
        $sortedSwitches = $engine->getEngineSwitchDefinitions()->getSwitchesInDisplayOrder();

        // shorthand
        $op = $engine->output;
        $so = $engine->output->stdout;

        $so->output($op->highlightStyle, $engine->getAppName());
        $so->output(null, ' -');
        $this->showSwitchSummary($op, $so, $sortedSwitches);
        $so->outputLine(null, ' [ command ] [ command-options ]');
    }

    public function showLongHelp(CliEngine $engine)
    {
        // get the list of switches in display order
        $sortedSwitches = $engine->getEngineSwitchDefinitions()->getSwitchesInDisplayOrder();

        // shorthand
        $op = $engine->output;
        $so = $engine->output->stdout;

        $so->output($op->highlightStyle, $engine->getAppName() . ' ' . $engine->getAppVersion());
        $so->output(null, ' -');
        $so->outputLine($op->urlStyle, ' ' . $engine->getAppUrl());
        $so->outputLine(null, $engine->getAppCopyright());
        $so->outputLine(null, $engine->getAppLicense());
        $so->outputBlankLine();
        $this->showSynopsis($op, $so, $engine->getAppName(), $sortedSwitches);
        $this->showOptionsList($op, $so, $sortedSwitches);
        $this->showCommandsList($op, $so, $engine->getAppName(), $engine->getCommandsList());
    }

    protected function showSynopsis($op, $so, $appName, $sortedSwitches)
    {
        $so->outputLine(null, 'SYNOPSIS');
        $so->setIndent(4);
        $so->output($op->commandStyle, $appName);

        $this->showSwitchSummary($op, $so, $sortedSwitches);

        $so->outputLine(null, ' [ command ] [ command-options ]');
        $so->outputBlankLine();
    }

    protected function showOptionsList($op, $so, $sortedSwitches)
    {
        $so->setIndent(0);
        $so->outputLine(null, 'OPTIONS');
        $so->addIndent(4);
        $so->outputLine(null, 'Use the following switches in front of any <command> to have the following effects.');
        $so->outputBlankLine();
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
            $this->showSwitchLongDetails($op, $so, $switch);
        }
    }

    protected function showCommandsList($op, $so, $appName, $commandsList)
    {
        // how many commands are there?
        $noOfCommands = count($commandsList);

        // what do we need to show the user?
        if ($noOfCommands == 0)
        {
            $this->showNoCommandsList($op, $so, $appName);
        }
        else
        {
            $this->showManyCommandsList($op, $so, $appName, $commandsList);
        }
    }

    protected function showNoCommandsList($op, $so, $appName)
    {
        $so->setIndent(0);
        $so->outputLine(null, 'COMMANDS');
        $so->addIndent(4);

        $so->output("At this moment in time, ");
        $so->output($op->commandStyle, $appName);
        $so->outputLine(" hasn't defined any commands for you to run, sorry!");
    }

    protected function showManyCommandsList($op, $so, $appName, $commandsList)
    {

        $so->setIndent(0);
        $so->outputLine(null, 'COMMANDS');
        $so->addIndent(4);

        ksort($commandsList);

        // work out our longest command name length
        $maxlen = 0;
        foreach ($commandsList as $commandName => $command)
        {
            if (strlen($commandName) > $maxlen)
            {
                $maxlen = strlen($commandName);
            }
        }

        foreach ($commandsList as $commandName => $command)
        {
            $so->output($op->commandStyle, $commandName);
            $so->addIndent($maxlen + 1);
            $so->output($op->commentStyle, '# ');
            $so->addIndent(2);
            $so->outputLine(null, $command->getShortDescription());
            $so->addIndent(0 - $maxlen - 3);
        }

        $so->outputBlankLine();
        $so->output(null, 'See ');
        $so->output($op->commandStyle, $appName . ' help <command>');
        $so->outputLine(null, ' for detailed help on <command>');
    }

    protected function showSwitchSummary($op, $so, $sortedSwitches)
    {
        if (count($sortedSwitches['shortSwitchesWithoutArgs']) > 0)
        {
            $so->output(null, ' [ ');
            $so->output($op->switchStyle, '-' . implode(' -', $sortedSwitches['shortSwitchesWithoutArgs']));
            $so->output(null, ' ]');
        }

        if (count($sortedSwitches['longSwitchesWithoutArgs']) > 0)
        {
            $so->output(null, ' [ ');
            $so->output($op->switchStyle, '--' . implode(' --', $sortedSwitches['longSwitchesWithoutArgs']));
            $so->output(null, ' ]');
        }

        if (count($sortedSwitches['shortSwitchesWithArgs']) > 0)
        {
            foreach ($sortedSwitches['shortSwitchesWithArgs'] as $shortSwitch => $switch)
            {
                $so->output(null, ' [ ');
                $so->output($op->switchStyle, '-' . $shortSwitch . ' ');
                $so->output($op->argStyle, $switch->arg->name);
                $so->output(null, ' ]');
            }
        }

        if (count($sortedSwitches['longSwitchesWithArgs']) > 0)
        {
            foreach ($sortedSwitches['longSwitchesWithArgs'] as $longSwitch => $switch)
            {
                $so->output(null, ' [ ');
                if ($switch->testHasArgument())
                {
                    $so->output($op->switchStyle, '--' . $longSwitch . '=');
                    $so->output($op->argStyle, $switch->arg->name);
                }
                $so->output(null, ' ]');
            }
        }
    }

    protected function showSwitchLongDetails($op, $so, DefinedSwitch $switch)
    {
        $shortOrLongSwitches = $switch->getHumanReadableSwitchList();
        $append = false;

        foreach ($shortOrLongSwitches as $shortOrLongSwitch)
        {
            if ($append)
            {
                $so->output(null, ' | ');
            }
            $append = true;

            $so->output($op->switchStyle, $shortOrLongSwitch);

            // is there an argument?
            if ($switch->testHasArgument())
            {
                if ($shortOrLongSwitch{1} == '-')
                {
                    $so->output(null, '=');
                }
                else
                {
                    $so->output(null, ' ');
                }
                $so->output($op->argStyle, $switch->arg->name);
            }
        }

        $so->outputLine(null, '');
        $so->addIndent(4);
        $so->outputLine(null, $switch->desc);
        if (isset($switch->longdesc))
        {
            $so->outputBlankLine();
            $so->outputLine(null, $switch->longdesc);
        }

        // output the default argument, if it is set
        if ($switch->testHasArgument() && isset($switch->arg->defaultValue))
        {
            $so->outputBlankLine();
            $so->output(null, 'The default value for ');
            $so->output($op->argStyle, $switch->arg->name);
            $so->output(null, ' is: ');
            $so->outputLine($op->exampleStyle, $switch->arg->defaultValue);
        }

        $so->addIndent(-4);
        $so->outputBlankLine();
    }
}