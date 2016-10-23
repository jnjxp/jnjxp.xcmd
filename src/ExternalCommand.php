<?php
/**
 * XCMD
 *
 * PHP version 5
 *
 * Copyright (C) 2016 Jake Johns
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 *
 * @category  Command
 * @package   Jnjxp\Xcmd
 * @author    Jake Johns <jake@jakejohns.net>
 * @copyright 2016 Jake Johns
 * @license   http://jnj.mit-license.org/2016 MIT License
 * @link      https://github.com/jnjxp/jnjxp.xcmd 
 */

namespace Jnjxp\Xcmd;

/**
 * External Command
 *
 * @category Command
 * @package  Jnjxp\Xcmd
 * @author   Jake Johns <jake@jakejohns.net>
 * @license  http://jnj.mit-license.org/ MIT License
 * @link     https://github.com/jnjxp/jnjxp.xcmd
 */
class ExternalCommand
{
    /**
     * Descriptors
     *
     * An indexed array where the key represents the descriptor number and the
     * value represents how PHP will pass that descriptor to the child process. 0
     * is stdin, 1 is stdout, while 2 is stderr.
     *
     * @var array
     *
     * @access protected
     */
    protected $descriptors = [
        0 => [ "pipe", "r" ],  // stdin : pipe to read from
        1 => [ "pipe", "w" ],  // stdout: pipe to write to
        2 => [ "pipe", "w" ]   // stderr: pipe to write to
    ];

    /**
     * Current Working Directory
     *
     * The initial working dir for the command. This must be an absolute
     * directory path, or NULL if you want to use the default value (the working
     * dir of the current PHP process)
     *
     * @var null|string
     *
     * @access protected
     */
    protected $cwd = null;

    /**
     * ENV
     *
     * An array with the environment variables for the command that will be run,
     * or NULL to use the same environment as the current PHP process
     *
     * @var array
     *
     * @access protected
     */
    protected $env = [];

    /**
     * Command
     *
     * The command to execute
     *
     * @var string
     *
     * @access protected
     */
    protected $command;

    /**
     * Payload
     *
     * @var Payload
     *
     * @access protected
     */
    protected $payload;

    /**
     * Throw Exception on failure?
     *
     * @var bool
     *
     * @access protected
     */
    protected $throwException = false;

    /**
     * Pipes
     *
     * Will be set to an indexed array of file pointers that correspond to PHP's
     * end of any pipes that are created.
     *
     * @var mixed
     *
     * @access protected
     */
    protected $pipes;

    /**
     * Resource
     *
     * A resource representing the process
     *
     * @var resource|false
     *
     * @access protected
     */
    protected $resource;

    /**
     * Input
     *
     * @var string|null
     *
     * @access protected
     */
    protected $input;

    /**
     * Output
     *
     * @var string
     *
     * @access protected
     */
    protected $output;

    /**
     * Error
     *
     * @var string
     *
     * @access protected
     */
    protected $error;

    /**
     * Exit Status
     *
     * @var int
     *
     * @access protected
     */
    protected $exitStatus;

    /**
     * __construct
     *
     * @param string $command command to run
     *
     * @access public
     */
    public function __construct($command)
    {
        $this->command = $command;
        $this->payload = new Payload;
    }

    /**
     * Set CWD
     *
     * @param mixed $path path to CWD
     *
     * @return null
     *
     * @access public
     */
    public function cwd($path)
    {
        $this->cwd = $path;
    }

    /**
     * Set ENV
     *
     * @param array $env environment
     *
     * @return null
     *
     * @access public
     */
    public function env(array $env)
    {
        $this->env = $env;
    }

    /**
     * Throw Exception?
     *
     * @param bool $bool set true to throw exception on non-zero exit
     *
     * @return null
     *
     * @access public
     */
    public function throwException($bool = true)
    {
        $this->throwException = (bool) $bool;
    }

    /**
     * __invoke
     *
     * @param null|string $input command input
     *
     * @return Payload
     *
     * @throws Exception if process not created
     * @throws Exception if ThrowException=true & invalid exit status
     *
     * @access public
     */
    public function __invoke($input = null)
    {
        $this->input = $input;

        $this->openProcess();
        $this->sendInput();
        $this->readOutput();
        $this->readError();
        $this->closeProcess();

        return $this->returnPayload();
    }

    /**
     * Return Payload
     *
     * @return Payload
     *
     * @access protected
     */
    protected function returnPayload()
    {
        return $this->payload()
            ->setStatus($this->exitStatus)
            ->setInput($this->input)
            ->setOutput(trim($this->output))
            ->setMessages($this->formatErrors())
            ->setExtras(
                [
                    'command' => $this->command,
                    'cwd'     => $this->cwd,
                    'env'     => $this->env
                ]
            );
    }

    /**
     * Open Process
     *
     * @return null
     *
     * @throws Exception if process not created
     *
     * @access protected
     */
    protected function openProcess()
    {
        $this->resource = proc_open(
            $this->command,
            $this->descriptors,
            $this->pipes,
            $this->cwd,
            $this->env
        );

        // @codeCoverageIgnoreStart
        if (! is_resource($this->resource)) {
            throw new Exception('proc_open failed');
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Send Input
     *
     * @return null
     *
     * @access protected
     */
    protected function sendInput()
    {
        $stdin = $this->pipes[0];
        if (null !== $this->input) {
            fwrite($stdin, $this->input);
        }

        fclose($stdin);
    }

    /**
     * Read Output
     *
     * @return null
     *
     * @access protected
     */
    protected function readOutput()
    {
        $stdout = $this->pipes[1];
        $this->output = stream_get_contents($stdout);
        fclose($stdout);
    }

    /**
     * Read Error
     *
     * @return null
     *
     * @access protected
     */
    protected function readError()
    {
        $stderr = $this->pipes[2];
        $this->error  = stream_get_contents($stderr);
        fclose($stderr);
    }

    /**
     * Close Process
     *
     * @return null
     * @throws Exception if throwException = true & invalid exit status
     *
     * @access protected
     */
    protected function closeProcess()
    {
        $this->exitStatus = proc_close($this->resource);
        $this->assertValidStatus();
    }

    /**
     * Assert Valid Status
     *
     * @return null
     *
     * @throws Exception if throwException = true & exit status > 0
     *
     * @access protected
     */
    protected function assertValidStatus()
    {
        if ($this->throwException && $this->exitStatus > 0) {
            throw new Exception($this->error, $this->exitStatus);
        }
    }

    /**
     * Format Errors
     *
     * @return array
     *
     * @access protected
     */
    protected function formatErrors()
    {
        if (!$this->error) {
            return [];
        }
        return array_filter(explode("\n", $this->error));
    }

    /**
     * Payload
     *
     * @return Payload
     *
     * @access protected
     */
    protected function payload()
    {
        return clone $this->payload;
    }
}
