<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Db\TableGateway\TableGateway;	

	return array(
		'service_manager' => array(
			'factories' => array(
				'Album\Model\DAO\Album' =>  function($sm) {
					return new Album\Model\DAO\Album(
						$sm->get('AlbumTableGateway'), $sm->get('predis')
					);
				},
				'AlbumTableGateway' => function ($sm) {
					$resultSetPrototype = new ResultSet();

					return new TableGateway(
						'album', 
						$sm->get('Zend\Db\Adapter\Adapter')
					);
				},
				'Zend\Db\Adapter\Adapter'
						=> 'Zend\Db\Adapter\AdapterServiceFactory',
			),
		)
	);