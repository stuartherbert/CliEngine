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

namespace Phix_Project\CliEngine\Switches;

use Phix_Project\CliEngine;
use Phix_Project\CliEngine\CliEngineSwitch;

use Phix_Project\ValidationLib4\Type_MustBeIntegerInRange;

/**
 * A nice generic '--verbose' switch for your CLI tool
 *
 * @package     Phix_Project
 * @subpackage  CliEngine
 * @author      Stuart Herbert <stuart@stuartherbert.com>
 * @copyright   2013-present Stuart Herbert. www.stuartherbert.com
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://www.phix-project.org
 * @version     @@PACKAGE_VERSION@@
 */
class VerboseLongSwitch extends CliEngineSwitch
{
	protected $min;
	protected $max;

	public function __construct($engineOptions, $min, $max)
	{
		// remember our range
		$this->min = $min;
		$this->max = $max;

		// set the default for our 'verbosity' level
		$engineOptions->verbosity = $min;
	}

	public function getDefinition()
	{
		// define our name, and our description
		$def = $this->newDefinition('longVerbose', 'increase amount of information shown');

		// there are no short switches

		// what are the long switches?
		$def->addLongSwitch('verbose');

		// this switch has an optional parameter
		$def->setOptionalArg('level', 'how verbose to be');
		$def->setArgHasDefaultValueOf($this->min);
		$def->setArgValidator(new Type_MustBeIntegerInRange($this->min, $this->max));

		// all done
		return $def;
	}

	public function process(CliEngine $engine, $invokes = 1, $params = array(), $isDefaultParam = false)
	{
		// set the verbosity level, but only if we're not being given
		// our default parameter
		//
		// this makes sure that using the -V short-switch doesn't cause
		// any problems
		if ($isDefaultParam)
		{
			return CliEngine::PROCESS_CONTINUE;
		}

		// set the new verbosity level
		$engine->options->verbosity = $params[0];

		// tell the engine to carry on
		return CliEngine::PROCESS_CONTINUE;
	}
}