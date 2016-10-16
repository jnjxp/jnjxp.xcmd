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
 * @link      http://jakejohns.net
 */

namespace Jnjxp\Xcmd;

/**
 * External Command
 *
 * @category Command
 * @package  Jnjxp\Xcmd
 * @author   Jake Johns <jake@jakejohns.net>
 * @license  http://jnj.mit-license.org/ MIT License
 * @link     http://jakejohns.net
 */
class ExternalCommand
{
    /**
     * Descriptors
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
     * @var null|string
     *
     * @access protected
     */
    protected $cwd = null;

    /**
     * ENV
     *
     * @var array
     *
     * @access protected
     */
    protected $env = [];

    /**
     * Command
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
     * @var mixed
     *
     * @access protected
     */
    protected $pipes;

    /**
     * Resource
     *
     * @var mixed
     *
     * @access protected
     */
    protected $resource;

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
     * @throws Exception if process not created or throwException is true
     *
     * @access public
     */
    public function __invoke($input = null)
    {
        $this->openProcess();
        $this->assertResource();

        $this->sendInput($input);
        $output = $this->readOutput();
        $error  = $this->readError();
        $status = $this->getStatus();
        $this->assertValidStatus($status, $error);

        return $this->payload()
            ->setStatus($status)
            ->setInput($input)
            ->setOutput(trim($output))
            ->setMessages($this->formatErrors($error))
            ->setExtras(
                [
                    'command' => $this->command,
                    'cwd'     => $this->cwd,
                    'env'     => $this->env
                ]
            );
    }

    /**
     * OpenProcess
     *
     * @return \Resource
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
    }

    /**
     * AssertResource
     *
     * @return mixed
     * @throws exceptionclass [description]
     *
     * @access protected
     */
    protected function assertResource()
    {
        // @codeCoverageIgnoreStart
        if (! is_resource($this->resource)) {
            throw new Exception('proc_open failed');
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * AssertValidStatus
     *
     * @param mixed $status DESCRIPTION
     * @param mixed $error  DESCRIPTION
     *
     * @return mixed
     * @throws exceptionclass [description]
     *
     * @access protected
     */
    protected function assertValidStatus($status, $error)
    {
        if ($this->throwException && $status > 0) {
            throw new Exception($error, $status);
        }
    }

    /**
     * SendInput
     *
     * @param mixed $input DESCRIPTION
     *
     * @return mixed
     * @throws exceptionclass [description]
     *
     * @access protected
     */
    protected function sendInput($input = null)
    {
        $stdin = $this->pipes[0];
        if (null !== $input) {
            fwrite($stdin, $input);
        }

        fclose($stdin);
    }

    /**
     * ReadOutput
     *
     * @return mixed
     *
     * @access protected
     */
    protected function readOutput()
    {
        $stdout = $this->pipes[1];
        $out = stream_get_contents($stdout);
        fclose($stdout);
        return $out;
    }

    /**
     * ReadErrors
     *
     * @return mixed
     *
     * @access protected
     */
    protected function readError()
    {
        $stderr = $this->pipes[2];
        $error  = stream_get_contents($stderr);
        fclose($stderr);
        return $error;
    }

    /**
     * GetStatus
     *
     * @return mixed
     * @throws exceptionclass [description]
     *
     * @access protected
     */
    protected function getStatus()
    {
        return proc_close($this->resource);
    }

    /**
     * FormatErrors
     *
     * @param mixed $error DESCRIPTION
     *
     * @return mixed
     *
     * @access protected
     */
    protected function formatErrors($error)
    {
        if (!$error) {
            return [];
        }
        return array_filter(explode("\n", $error));
    }

    /**
     * Payload
     *
     * @return mixed
     *
     * @access protected
     */
    protected function payload()
    {
        return clone $this->payload;
    }
}
