<?php
namespace Album\Controller;

use Album\Model\Entity\Album as Album,
 	Zend\Mvc\Controller\AbstractActionController,
    Zend\View\Model\ViewModel;

class AlbumController extends AbstractActionController
{
	private $albumTable;
	
	public function indexAction()
	{
		var_dump($this->getAlbumTable()->fetchAll());
		return new ViewModel(array(
			'albums' => $this->getAlbumTable()->fetchAll(),
		));
	}

	public function addAction()
	{
	
	}

	public function editAction()
	{
	}

	public function deleteAction()
	{
	
	}
	
	private function getAlbumTable()
	{
		if (!$this->albumTable) {
			$sm = $this->getServiceLocator();
			$this->albumTable = $sm->get('Album\Model\DAO\Album');
		}
		return $this->albumTable;
	}
}