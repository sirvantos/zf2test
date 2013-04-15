<?php
	namespace Application\Model\Cache\Predis;

	use 
		Predis\Client as PClient,
		Zend\Barcode\Object\Exception\RuntimeException,
		Zend\Db\TableGateway\TableGateway;
	/**
	 * Description of Client
	 *
	 * @author sirvantos
	 */
	final class Client extends PClient
	{
		const TEMPLATE_TYPE			= '|{VALUE}|';
		const TEMPLATE_KEY			= '&{VALUE}&';
		
		const DEFAULT_EXPIRE		= 86400;
		
		const KEY_TYPE_LIST			= 1;
		const KEY_TYPE_TAGS			= 2;
		const KEY_TYPE_KEY_TAGS		= 3;
		const KEY_TYPE_OBJECTS		= 4;
		
		private $defaultExpire  = self::DEFAULT_EXPIRE;
		
		/**
		 * @var string
		 */
		private $namespace = null;
		
		/**
		* Initializes a new client with optional connection parameters and client options.
		*
		* @param mixed $parameters Connection parameters for one or multiple servers.
		* @param mixed $options Options that specify certain behaviours for the client.
		*/
		public function __construct($parameters = null, $options = null)
		{
			parent::__construct($parameters, $options);
			
			if (!empty($parameters['default_expire'])) 
			{
				$this->defaultExpire = $parameters['default_expire'];
			}
		}
		
		/**
		 * @param String
		 * @return Client
		 */
		public function setNamespace($namespace)
		{
			$this->namespace = $namespace;
			
			return $this;
		}
		
		public function setObject2Cache(
			Array $struct, $tags = array(), $expire = self::DEFAULT_EXPIRE
		)
		{
			if (empty($struct['id'])) 
				throw new RuntimeException(
					'Knows nothing about id for struct'
				);
			
			$hashKey = $this->makeObjectCacheKey($struct['id']);
			
			$this->hmset($hashKey, $struct);
			
			if ($expire) $this->setExpire($hashKey, $expire);
			
			return $this;
		}
		
		public function cacheObjectExists($id)
		{
			return $this->exists($this->makeObjectCacheKey($id));
		}
		
		public function removeObjectFromCache($id)
		{
			return $this->del($this->makeObjectCacheKey($id));
		}
		
		public function getObjectFromCache($id)
		{
			return $this->hgetall($this->makeObjectCacheKey($id));
		}
		
		/**
		 * @param String $key
		 * @return null | Array
		 */
		public function getListFromCache($key)
		{
			$cacheKey = $this->makeNamespacedCacheKey($key);
			
			if ($keys = $this->getKeysByKey($cacheKey)) {
				
				$result = array();
				
				foreach ($keys as $listKey) {
					$res = $this->hgetall(
						$this->makeObjectCacheKey($listKey)
					);
					
					if ($res) {
						$result[] = $res;
					} else {
						$this->lrem($cacheKey, 1, $listKey);
					}
				}
				
				return $result;
			}
			
			return null;
		}
		
		/**
		 * @param String $key
		 * @param array $list
		 * @param Int | null $expire
		 * @return Client
		 */
		public function setList2Cache(
			$key, Array $list, Array $tags = array(), $expire = self::DEFAULT_EXPIRE
		)
		{
			if (!$list) return array();
			
			$this->removeListFromCache($key);
			
			foreach ($list as $row) 
			{
				$this->pushObject2CacheList($key, $row);
			}
			
			$hashKey	= $this->makeListCacheKey($key);
			
			if ($expire) { 
				$this->setExpire ($hashKey, $expire);
			}
			
			$this->applyTagsFor($tags, $key, $hashKey);
			
			return $this;
		}
		
		public function pushObject2CacheList($key, Array $object)
		{
			$hashKeyId	= $this->makeObjectCacheKey($object['id']);
			
			if ($this->exists($hashKeyId)) $this->del($hashKeyId);
			
			$this->rpush($this->makeListCacheKey($key), $object['id']);
			$this->hmset($hashKeyId, $object);
			
			return $this;
		}
		
		public function popObjectFromCacheList($key)
		{
			if ($res = $this->lpop($this->makeListCacheKey($key))) 
				return $this->hgetall($this->makeListCacheKey($res));
			
			return false;
		}
		
		public function cacheListExists($key)
		{
			return $this->exists($this->makeListCacheKey($key));
		}
		
		/**
		 * @param String $key
		 * @return boolean
		 */
		public function removeListFromCache($key)
		{
			$hashKey = $this->makeListCacheKey($key);
			
			$this->removeListFromCacheByHash(
				$this->makeListCacheKey($key)
			);
			
			$this->removeTagsForKey($key, $this->getKeyTags($key));
			
			return $this;
		}
		
		public function getKeyTags($key)
		{
			return $this->getKeysByKey(
				$this->makeCacheKey($key, self::KEY_TYPE_KEY_TAGS)
			);
		}
		
		public function removeCacheByTags(Array $tags)
		{
			foreach ($this->makeTagCacheKeys($tags) as $cacheTagKey) 
			{
				foreach ($this->getKeysByKey($cacheTagKey) as $cacheKey) 
				{
					switch ($this->determineCacheTypeByKey($cacheKey)) 
					{
						case self::KEY_TYPE_LIST:
						default:
							$this->removeListFromCacheByHash($cacheKey);
					}
				}
			}
		}
		
		public function removeTagsForKey($key, Array $tags)
		{
			if (!$tags) return false;
			
			$countOfDeleted = 0;
			
			foreach ($this->makeTagCacheKeys($tags) as $tagHashKey) 
			{
				if ($this->srem($tagHashKey, $key)) $i++;
			}
			
			return $countOfDeleted != 0;
		}
		
		/**
		 * @param Integer $key
		 * @param String $expire
		 * @return Client
		 * @throws \Zend\Barcode\Object\Exception\RuntimeException
		 */
		private function setExpire(/*String*/ $hashKey, /*Int*/ $expire = null)
		{
			if ($expire === null) $expire = $this->defaultExpire;
			
			if (is_string($expire)) $expire = strtotime($expire) - time();
			
			if (!is_int($expire)) {
				throw new \Zend\Barcode\Object\Exception\RuntimeException(
					'Wrong expire given >>' . $expire . '<<'
				);
			}
			
			$this->expire($hashKey, $expire);
			
			return $this;
		}
		
		private function determineCacheTypeByKey($cacheKey)
		{
			return self::KEY_TYPE_LIST;
		}
		
		private function removeListFromCacheByHash($hash)
		{
			if ($this->exists($hash))  {
				
				$this->del($hash);
				
				return true;
			}
			
			return false;
		}
		
		private function applyTagsFor(Array $tags, $key, $hashKey)
		{
			if (!$tags) return array();
			
			$hashKeyTags = $this->makeCacheKey($key, self::KEY_TYPE_KEY_TAGS);
			
			foreach ($this->makeTagCacheKeys($tags) as $tagHashKey) {
				$this->rpush($tagHashKey, $hashKey);
				$this->rpush($hashKeyTags, $tagHashKey);
			}
			
			$this->persist($tagHashKey);
			$this->persist($hashKeyTags);
			
			return $this;
		}
		
		/**
		 * @param String $key
		 * @return Array
		 */
		private function getKeysByKey($cacheKey)
		{
			return $this->lrange($cacheKey, 0, -1);
		}
		
		/**
		 * @param String $keyName
		 * @return String
		 */
		private function makeCacheKey($keyName, $type = self::KEY_TYPE_LIST)
		{
			//table prefix
			$key = str_replace('{VALUE}', $keyName, self::TEMPLATE_KEY);
			
			$typeStr = '';
			
			switch ($type) {
				case self::KEY_TYPE_KEY_TAGS:
					$typeStr = 'keytag';
					break;
				case self::KEY_TYPE_TAGS:
					$typeStr = 'tag';
					break;
				case self::KEY_TYPE_OBJECTS:
					$typeStr = 'object';
					break;
				case self::KEY_TYPE_LIST:
				default:
					$typeStr = 'list';
			}
			
			$key .= str_replace('{VALUE}', $typeStr, self::TEMPLATE_TYPE);
			
			return $key;
		}
		
		private function makeNamespacedCacheKey(
			$keyName, $type = self::KEY_TYPE_LIST
		)
		{
			return 
				md5(strtolower($this->namespace)) 
				. $this->makeCacheKey($keyName, $type);
		}
		
		/**
		 * @param String $keyName
		 * @return String
		 */
		private function makeListCacheKey($keyName)
		{
			return $this->makeNamespacedCacheKey(
				$keyName, self::KEY_TYPE_LIST
			);
		}
		
		/**
		 * @param String $keyName
		 * @return String
		 */
		private function makeObjectCacheKey($id)
		{
			return $this->makeNamespacedCacheKey(
				$id, self::KEY_TYPE_OBJECTS
			);
		}
		
		/**
		 * @param String $keyName
		 * @return String
		 */
		private function makeTagCacheKeys($keyName, array $tags)
		{
			return $this->makeCacheKeys($keyName, $tags);
		}
		
		/**
		 * 
		 */
		private function makeCacheKeys(
			$keyName, array $tags, $type =  self::KEY_TYPE_TAGS
		)
		{
			$cacheKeys = array();
			
			foreach ($tags as $tag) 
			{
				$cacheKeys[] = $this->makeCacheKey($keyName, $type, $tag);
			}
			
			return $cacheKeys;
		}
	}
?>