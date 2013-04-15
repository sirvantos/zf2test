<?php
	namespace AlbumTest\Model\Entity;

	use Album\Model\Entity\Album;
	use PHPUnit_Framework_TestCase;

	class AlbumTest extends PHPUnit_Framework_TestCase
	{
		public function testAlbumInitialState()
		{
			$album = new Album();

			$this->assertNull($album->getArtist(), '"artist" should initially be null');
			$this->assertNull($album->getId(), '"id" should initially be null');
			$this->assertNull($album->getTitle(), '"title" should initially be null');
		}

		public function testExchangeArraySetsPropertiesCorrectly()
		{
			$album = new Album();
			$data  = array('artist' => 'some artist',
						   'id'     => 123,
						   'title'  => 'some title');

			$album->exchangeArray($data);

			$this->assertSame($data['artist'], $album->getArtist(), '"artist" was not set correctly');
			$this->assertSame($data['id'], $album->getId(), '"id" was not set correctly');
			$this->assertSame($data['title'], $album->getTitle(), '"title" was not set correctly');
		}

		public function testExchangeArraySetsPropertiesToNullIfKeysAreNotPresent()
		{
			$album = new Album();

			$album->exchangeArray(array('artist' => 'some artist',
										'id'     => 123,
										'title'  => 'some title'));
			$album->exchangeArray(array());

			$this->assertNull($album->getArtist(), '"artist" should have defaulted to null');
			$this->assertNull($album->getId(), '"id" should have defaulted to null');
			$this->assertNull($album->getTitle(), '"title" should have defaulted to null');
		}
	}