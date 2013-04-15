<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
use 
	Zend\Db\TableGateway\TableGateway,
	Application\Bootstrap,
	Application\Model\Cache\Predis\Client as ClientP;

	/**
	 * Description of ClientTest
	 *
	 * @author sirvantos
	 */
	class ClientTest extends PHPUnit_Framework_TestCase 
	{
		private $predis = null;
		
		function __construct() {
			parent::__construct();
			
			$sm = Bootstrap::getServiceManager();
			
			$this->predis = $sm->get('predis')->setNamespace('&%&superTest&%&');
		}
		
		public function testCacheListBase()
		{
			$array = array(
				array(
					'id'	=> 1,
					'name'	=> 'vladimir',
					'desc'	=> 'Hello Vladimir',
				),
				array(
					'id'	=> 2,
					'name'	=> 'Chali',
					'desc'	=> 'Hello Chali',
				),
				array(
					'id'	=> 3,
					'name'	=> 'Dennis',
					'desc'	=> 'Hello Dennis',
				),
			);
			
			$this->predis->removeListFromCache('unitTestCache');
			
			$this->assertFalse(
				$this->predis->cacheListExists('unitTestCache'), 
				'Can not remove cache'
			);
			
			$this->predis->setList2Cache('unitTestCache', $array);
			
			$this->assertTrue(
				$this->predis->cacheListExists('unitTestCache'), 
				'Can not set cache'
			);
			
			$this->assertEquals(
				$array, $this->predis->getListFromCache('unitTestCache')
			);
			
			$this->predis->removeListFromCache('unitTestCache');
		}
		
		public function testListExpire()
		{
			$array = array(
				array(
					'id'	=> 1,
					'name'	=> 'vladimir',
					'desc'	=> 'Hello Vladimir',
				),
				array(
					'id'	=> 2,
					'name'	=> 'Chali',
					'desc'	=> 'Hello Chali',
				),
				array(
					'id'	=> 3,
					'name'	=> 'Dennis',
					'desc'	=> 'Hello Dennis',
				),
			);
			
			$this->predis->removeListFromCache('unitTestCache');
			
			$this->assertFalse(
				$this->predis->cacheListExists('unitTestCache'), 
				'Can not remove cache'
			);
			
			$this->predis->setList2Cache('unitTestCache', $array, array(),  1);
			
			$this->assertTrue(
				$this->predis->cacheListExists('unitTestCache'), 
				'The list is missing'
			);
			
			sleep(2);
			
			$this->assertFalse(
				$this->predis->cacheListExists('unitTestCache'), 
				'No list expire'
			);
		}
		
		public function testStructureIdBase()
		{
			$array = array(
				array(
					'id'	=> 1,
					'name'	=> 'vladimir',
					'desc'	=> 'Hello Vladimir',
				),
				array(
					'id'	=> 2,
					'name'	=> 'Chali',
					'desc'	=> 'Hello Chali',
				),
				array(
					'id'	=> 3,
					'name'	=> 'Dennis',
					'desc'	=> 'Hello Dennis',
				),
			);
			
			$this->predis->removeObjectFromCache($array[0]['id']);
			$this->predis->removeObjectFromCache($array[1]['id']);
			$this->predis->removeObjectFromCache($array[2]['id']);
			
			$this->assertFalse(
				$this->predis->cacheObjectExists($array[0]['id']), 
				'Can not remove cache'
			);
			
			$this->assertFalse(
				$this->predis->cacheObjectExists($array[1]['id']), 
				'Can not remove cache'
			);
			
			$this->assertFalse(
				$this->predis->cacheObjectExists($array[2]['id']), 
				'Can not remove cache'
			);
			
			$this->predis->setObject2Cache($array[0]);
			$this->predis->setObject2Cache($array[1]);
			$this->predis->setObject2Cache($array[2]);
			
			$this->assertEquals(
				$array[0],
				$this->predis->getObjectFromCache($array[0]['id']), 
				'Can GET object from cache'
			);
			
			$this->assertEquals(
				$array[1],
				$this->predis->getObjectFromCache($array[1]['id']), 
				'Can GET object from cache'
			);
			
			$this->assertEquals(
				$array[2],
				$this->predis->getObjectFromCache($array[2]['id']), 
				'Can GET object from cache'
			);
			
			$this->predis->removeObjectFromCache($array[0]['id']);
			$this->predis->removeObjectFromCache($array[1]['id']);
			$this->predis->removeObjectFromCache($array[2]['id']);
		}
		
		public function testStructureIdExpire()
		{
			$array = array(
				array(
					'id'	=> 1,
					'name'	=> 'vladimir',
					'desc'	=> 'Hello Vladimir',
				),
				array(
					'id'	=> 2,
					'name'	=> 'Chali',
					'desc'	=> 'Hello Chali',
				),
				array(
					'id'	=> 3,
					'name'	=> 'Dennis',
					'desc'	=> 'Hello Dennis',
				),
			);
			
			$this->predis->removeObjectFromCache($array[0]['id']);
			$this->predis->removeObjectFromCache($array[1]['id']);
			$this->predis->removeObjectFromCache($array[2]['id']);
			
			$this->assertFalse(
				$this->predis->cacheObjectExists($array[0]['id']), 
				'Can not remove cache'
			);
			
			$this->assertFalse(
				$this->predis->cacheObjectExists($array[1]['id']), 
				'Can not remove cache'
			);
			
			$this->assertFalse(
				$this->predis->cacheObjectExists($array[2]['id']), 
				'Can not remove cache'
			);
			
			$this->predis->setObject2Cache($array[0], array(), 1);
			$this->predis->setObject2Cache($array[1], array(), 1);
			$this->predis->setObject2Cache($array[2], array(), 1);
			
			sleep(2);
			
			$this->assertFalse(
				$this->predis->cacheObjectExists($array[0]['id']), 
				'Can not remove cache'
			);
			
			$this->assertFalse(
				$this->predis->cacheObjectExists($array[1]['id']), 
				'Can not remove cache'
			);
			
			$this->assertFalse(
				$this->predis->cacheObjectExists($array[2]['id']), 
				'Can not remove cache'
			);
		}
		
		public function testCrossListRemovingList()
		{
			$array = array(
				array(
					'id'	=> 1,
					'name'	=> 'vladimir',
					'desc'	=> 'Hello Vladimir',
				),
				array(
					'id'	=> 2,
					'name'	=> 'Chali',
					'desc'	=> 'Hello Chali',
				),
				array(
					'id'	=> 3,
					'name'	=> 'Dennis',
					'desc'	=> 'Hello Dennis',
				),
			);
			
			$this->predis->removeListFromCache('unitTestCache');
			
			$this->predis->setList2Cache('unitTestCache', $array);
			
			$this->assertTrue(
				$this->predis->cacheObjectExists($array[0]['id']), 
				'Knows nothing about id'
			);
			
			$this->assertTrue(
				$this->predis->cacheObjectExists($array[1]['id']), 
				'Knows nothing about id'
			);
			
			$this->assertTrue(
				$this->predis->cacheObjectExists($array[2]['id']), 
				'Knows nothing about id'
			);
			
			$this->predis->removeListFromCache('unitTestCache');
			
			$this->assertFalse(
				$this->predis->cacheListExists('unitTestCache'), 
				'list doesnt not remove'
			);
			
			$this->assertTrue(
				$this->predis->cacheObjectExists($array[0]['id']), 
				'Object does remove'
			);
			
			$this->assertTrue(
				$this->predis->cacheObjectExists($array[1]['id']), 
				'Object does remove'
			);
			
			$this->assertTrue(
				$this->predis->cacheObjectExists($array[2]['id']), 
				'Object does remove'
			);
			
			$this->predis->removeObjectFromCache($array[0]['id']);
			$this->predis->removeObjectFromCache($array[1]['id']);
			$this->predis->removeObjectFromCache($array[2]['id']);
		}
		
		public function testCrossListRemovingObject()
		{
			$array = array(
				array(
					'id'	=> 1,
					'name'	=> 'vladimir',
					'desc'	=> 'Hello Vladimir',
				),
				array(
					'id'	=> 2,
					'name'	=> 'Chali',
					'desc'	=> 'Hello Chali',
				),
				array(
					'id'	=> 3,
					'name'	=> 'Dennis',
					'desc'	=> 'Hello Dennis',
				),
			);
			
			$this->predis->removeListFromCache('unitTestCache');
			
			$this->predis->setList2Cache('unitTestCache', $array);
			
			$this->assertTrue(
				$this->predis->cacheObjectExists($array[0]['id']), 
				'Knows nothing about id'
			);
			
			$this->assertTrue(
				$this->predis->cacheObjectExists($array[1]['id']), 
				'Knows nothing about id'
			);
			
			$this->assertTrue(
				$this->predis->cacheObjectExists($array[2]['id']), 
				'Knows nothing about id'
			);
			
			$this->predis->removeObjectFromCache($array[2]['id']);
			$this->predis->removeObjectFromCache($array[0]['id']);
			
			$this->assertTrue(
				$this->predis->cacheListExists('unitTestCache'), 
				'list does remove'
			);
			
			$this->assertEquals(
				1,
				count($this->predis->getListFromCache('unitTestCache')), 
				'Wrong els count'
			);
			
			$this->assertFalse(
				$this->predis->cacheObjectExists($array[0]['id']), 
				'Object does remove'
			);
			
			$this->assertTrue(
				$this->predis->cacheObjectExists($array[1]['id']), 
				'Object does remove'
			);
			
			$this->assertFalse(
				$this->predis->cacheObjectExists($array[2]['id']), 
				'Object does remove'
			);
			
			$this->predis->removeObjectFromCache($array[0]['id']);
			$this->predis->removeObjectFromCache($array[1]['id']);
			$this->predis->removeObjectFromCache($array[2]['id']);
			
			$this->predis->removeListFromCache('unitTestCache');
		}
		
		public function checkPushPopListTest()
		{
			$array = array(
				array(
					'id'	=> 1,
					'name'	=> 'vladimir',
					'desc'	=> 'Hello Vladimir',
				),
				array(
					'id'	=> 2,
					'name'	=> 'Chali',
					'desc'	=> 'Hello Chali',
				),
				array(
					'id'	=> 3,
					'name'	=> 'Dennis',
					'desc'	=> 'Hello Dennis',
				),
			);
			
			$this->predis->removeListFromCache('unitTestCache');
			
			$this->predis->pushObject2CacheList('unitTestCache', $array[0]);
			
			$this->assertTrue(
				count($this->predis->getListFromCache('unitTestCache')) == 1, 
				'Object is not puashed'
			);
			
			$this->predis->pushObject2CacheList('unitTestCache', $array[1]);
			
			$this->assertTrue(
				count($this->predis->getListFromCache('unitTestCache')) == 2, 
				'Object is not puashed'
			);
			
			$this->predis->pushObject2CacheList('unitTestCache', $array[2]);
			
			$this->assertTrue(
				count($this->predis->getListFromCache('unitTestCache')) == 3, 
				'Object is not puashed'
			);
			
			for ($i = 0; $i < count($array); $i++) {
				$this->predis->pushObject2CacheList('unitTestCache', $array[$i]);
				
				$this->assertTrue(
					count($this->predis->getListFromCache('unitTestCache')) == ($i + 1), 
					'Object is not puashed'
				);
			}
			
			for ($i = 0; $i < count($array); $i++) {
				$this->assertTrue(
					$array[$i] == $this->predis->popObjectFromCacheList('unitTestCache')
				);
				
				$this->assertTrue(
					count($this->predis->getListFromCache('unitTestCache')) == 
					count($array) - ($i + 1), 
					'Object is not puashed'
				);
			}
		}
	}
?>
