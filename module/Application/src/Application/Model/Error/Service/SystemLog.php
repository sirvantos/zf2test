<?php
	namespace Application\Model\Error\Service;

	use Zend\EventManager\Event,
		Zend\Log\Logger, 
		Application\Model\Error\Logger\Convert2Exception;

	class SystemLog
	{
		/**
		 * @param \Zend\EventManager\Event $e
		 * @return \Application\Model\Error\Service\SystemLog
		 */
		public function initLog(Event $e)
		{
			Logger::registerErrorHandler(new Convert2Exception());
			
			return $this;
		}
	}
