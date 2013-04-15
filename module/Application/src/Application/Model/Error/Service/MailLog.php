<?php
	namespace Application\Model\Error\Service;
	
	use 
		Zend\EventManager\Event,
		Zend\Log\Logger, 
		Zend\Mail\Message;
	
	final class MailLog extends SystemLog
	{
		/**
		 * @param \Zend\EventManager\Event $e
		 * @return \Application\Model\Error\Service\MailLog
		 */
		public function initLog(Event $e)
		{
			parent::initLog($e);
			
			$message = new Message();
			
			$message->setTo('sirvantosbuglovers@gmail.com');
			
			$logger = new Logger(array('writers' => array(
				array(
					'name' => '\Zend\Log\Writer\Mail', 
					'options' => array(
						'mail' => $message
					)
				)
			)));
			
			$e->getApplication()->getEventManager()->attach(
				\Zend\Mvc\Application::ERROR_EXCEPTION, function (Event $e) use ($logger) {
					$logger->log(Logger::CRIT, $e->getParam('exception'));
				}
			);
			
			$e->getApplication()->getEventManager()->attach(
				\Zend\Mvc\MvcEvent::EVENT_RENDER_ERROR, function (Event $e) use ($logger) {
					$logger->log(Logger::CRIT, $e->getParam('exception'));
				}
			);
			
			$e->getApplication()->getEventManager()->attach(
				\Zend\Mvc\MvcEvent::EVENT_DISPATCH_ERROR, function (Event $e) use ($logger) {
					$logger->log(Logger::CRIT, $e->getParam('exception'));
				}
			);
			
			return $this;
		}
	}
