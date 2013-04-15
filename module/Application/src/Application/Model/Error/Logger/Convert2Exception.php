<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Model\Error\Logger;

use  Zend\Log\Logger;

/**
 * Description of Convert2Exception
 *
 * @author sirvantos
 */
final class Convert2Exception extends Logger 
{
	public function log($priority, $message, $extra = array()) 
	{
		throw new \Exception($message, $priority);
	}
}
