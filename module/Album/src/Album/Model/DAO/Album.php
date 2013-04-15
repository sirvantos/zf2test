<?php 
	namespace Album\Model\DAO;
	
	use 
		Zend\Db\TableGateway\TableGateway,
		Predis\Client as PClient;
	
	class Album extends AbstractDAO
	{
		public function fetchAll()
		{
			if (($res = $this->predis->getListFromCache('fetchall')) !== null) {
				return $res;
			}
			
			$res = parent::fetchAll()->toArray();
			
			if ($res) {
				$this->predis->setList2Cache('fetchall', $res);
			}
			
			return $res;
		}
		
		public function saveAlbum(Album $album)
		{
			$data = array(
				'artist' => $album->artist,
				'title'  => $album->title,
			);

			$id = (int)$album->id;
			if ($id == 0) {
				$this->tableGateway->insert($data);
			} else {
				if ($this->getAlbum($id)) {
					$this->tableGateway->update($data, array('id' => $id));
				} else {
					throw new \Exception('Form id does not exist');
				}
			}
		}
	}