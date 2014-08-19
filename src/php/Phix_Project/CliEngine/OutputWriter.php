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

use Phix_Project\CommandLineLib3\DefinedSwitches;
use Phix_Project\ConsoleDisplayLib4\ConsoleColor;
use Phix_Project\ConsoleDisplayLib4\StdOut;
use Phix_Project\ConsoleDisplayLib4\StdErr;

/**
 * Looks after all output on behalf of the CliEngine
 *
 * @package     Phix_Project
 * @subpackage  CliEngine
 * @author      Stuart Herbert <stuart@stuartherbert.com>
 * @copyright   2013-present Stuart Herbert. www.stuartherbert.com
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://www.phix-project.org
 * @version     @@PACKAGE_VERSION@@
 */

class OutputWriter
{
    public $argStyle = null;
    public $commentStyle = null;
    public $errorStyle = null;
    public $exampleStyle = null;
    public $highlightStyle = null;
    public $normalStyle = null;
    public $switchStyle = null;
    public $urlStyle = null;

    public $errorPrefix = null;

    public function __construct()
    {
        $this->stdout = new Stdout;
        $this->stderr = new Stderr;

        $this->setupStyles();
    }

    protected function setupStyles()
    {
        // shorthand
        $so = $this->stdout;

        // set the colours to use for our styles
        $this->argStyle = array(ConsoleColor::BOLD, ConsoleColor::BLUE_FG);
        $this->commandStyle = array(ConsoleColor::BOLD, ConsoleColor::GREEN_FG);
        $this->commentStyle = array(ConsoleColor::BLUE_FG);
        $this->errorStyle = array(ConsoleColor::BOLD, ConsoleColor::RED_FG);
        $this->exampleStyle = array(ConsoleColor::BOLD, ConsoleColor::YELLOW_FG);
        $this->highlightStyle = array(ConsoleColor::BOLD, ConsoleColor::GREEN_FG);
        $this->normalStyle = array(ConsoleColor::NONE);
        $this->switchStyle = array(ConsoleColor::BOLD, ConsoleColor::YELLOW_FG);
        $this->urlStyle = array(ConsoleColor::BOLD, ConsoleColor::BLUE_FG);

        // set up any prefixes that we want to use
        $this->errorPrefix = "*** error: ";
    }
}
