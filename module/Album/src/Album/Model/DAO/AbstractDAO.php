<?php 
	namespace Album\Model\DAO;
	
	use Zend\Db\TableGateway\TableGateway;
	use Application\Model\Cache\Predis\Client as PClient;
	
	abstract class AbstractDAO
	{
		/**
		 * @var PClient 
		 */
		protected $predis;
		
		/**
		 * @var Predis\Client
		 */
		protected $tableGateway;

		public function __construct(
			TableGateway $tableGateway, PClient $client
		)
		{
			$this->tableGateway = $tableGateway;
			$this->predis		= $client->setNamespace($tableGateway->getTable());
		}

		public function fetchAll()
		{
			return $this->tableGateway->select();
		}

		public function getAlbum($id)
		{
			$id  = (int) $id;
			$rowset = $this->tableGateway->select(array('id' => $id));
			$row = $rowset->current();
			
			if (!$row) {
				throw new \Exception("Could not find row $id");
			}
			
			return $row;
		}
		
		public function deleteAlbum($id)
		{
			return $this->tableGateway->delete(array('id' => $id));
		}
	}
