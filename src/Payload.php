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
 * @category  Payload
 * @package   Jnjxp\Xcmd
 * @author    Jake Johns <jake@jakejohns.net>
 * @copyright 2016 Jake Johns
 * @license   http://jnj.mit-license.org/2016 MIT License
 * @link      http://jakejohns.net
 */


namespace Jnjxp\Xcmd;

use Aura\Payload\Payload as BasePayload;

/**
 * Payload
 *
 * @category Payload
 * @package  Jnjxp\Xcmd
 * @author   Jake Johns <jake@jakejohns.net>
 * @license  http://jnj.mit-license.org/ MIT License
 * @link     http://jakejohns.net
 *
 * @see BasePayload
 */
class Payload extends BasePayload
{

    /**
     * Is Error?
     *
     * @return bool
     *
     * @access public
     */
    public function isError()
    {
        return $this->status > 0;
    }

    /**
     * Is Success?
     *
     * @return bool
     *
     * @access public
     */
    public function isSuccess()
    {
        return $this->status == 0;
    }

    /**
     * __toString
     *
     * @return string
     *
     * @access public
     */
    public function __toString()
    {
        return (string) $this->getOutput();
    }
}
