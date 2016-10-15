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
        $process = proc_open(
            $this->command,
            $this->descriptors,
            $pipes,
            $this->cwd,
            $this->env
        );

        // @codeCoverageIgnoreStart
        if (! is_resource($process)) {
            throw new Exception('proc_open failed');
        }
        // @codeCoverageIgnoreEnd

        $stdin  = $pipes[0];
        $stdout = $pipes[1];
        $stderr = $pipes[2];

        if (null !== $input) {
            fwrite($stdin, $input);
        }

        fclose($stdin);

        $out = stream_get_contents($stdout);
        fclose($stdout);

        $error = stream_get_contents($stderr);
        fclose($stderr);

        $status = proc_close($process);

        if ($this->throwException && $status > 0) {
            throw new Exception($error, $status);
        }

        return $this->payload()
            ->setStatus($status)
            ->setInput($input)
            ->setOutput(trim($out))
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
