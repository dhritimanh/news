<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt nukeawhale@gmail.com
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

namespace OCA\News\BusinessLayer;

require_once(__DIR__ . "/../../classloader.php");


use \OCA\News\Db\Folder;


class FolderBusinessLayerTest extends \OCA\AppFramework\Utility\TestUtility {

	protected $api;
	protected $folderMapper;
	protected $folderBusinessLayer;

	protected function setUp(){
		$this->api = $this->getAPIMock();
		$this->folderMapper = $this->getMockBuilder(
			'\OCA\News\Db\FolderMapper')
			->disableOriginalConstructor()
			->getMock();
		$this->folderBusinessLayer = new FolderBusinessLayer($this->folderMapper, $this->api);
	}


	function testFindAll(){
		$userId = 'jack';
		$return = 'hi';
		$this->folderMapper->expects($this->once())
			->method('findAllFromUser')
			->with($this->equalTo($userId))
			->will($this->returnValue($return));

		$result = $this->folderBusinessLayer->findAll($userId);

		$this->assertEquals($return, $result);
	}


	public function testCreate(){
		$folder = new Folder();
		$folder->setName('hey');
		$folder->setParentId(5);
		$folder->setUserId('john');
		$folder->setOpened(true);

		$this->folderMapper->expects($this->once())
			->method('insert')
			->with($this->equalTo($folder))
			->will($this->returnValue($folder));

		$result = $this->folderBusinessLayer->create('hey', 'john', 5);

		$this->assertEquals($folder, $result);
	}


	public function testCreateThrowsExWhenFolderNameExists(){
		$folderName = 'hihi';
		$rows = array(
			array('id' => 1)
		);

		$trans = $this->getMock('Trans', array('t'));
		$trans->expects($this->once())
			->method('t');
		$this->api->expects($this->once())
			->method('getTrans')
			->will($this->returnValue($trans));
		$this->folderMapper->expects($this->once())
			->method('findByName')
			->with($this->equalTo($folderName))
			->will($this->returnValue($rows));

		$this->setExpectedException('\OCA\News\BusinessLayer\BusinessLayerException');
		$result = $this->folderBusinessLayer->create($folderName, 'john', 3);
	}


	public function testOpen(){
		$folder = new Folder();

		$this->folderMapper->expects($this->once())
			->method('find')
			->with($this->equalTo(3))
			->will($this->returnValue($folder));

		$this->folderMapper->expects($this->once())
			->method('update')
			->with($this->equalTo($folder));

		$this->folderBusinessLayer->open(3, false, '');

		$this->assertFalse($folder->getOpened());

	}


	public function testRename(){
		$folder = new Folder();
		$folder->setName('jooohn');

		$this->folderMapper->expects($this->once())
			->method('find')
			->with($this->equalTo(3))
			->will($this->returnValue($folder));

		$this->folderMapper->expects($this->once())
			->method('update')
			->with($this->equalTo($folder));

		$this->folderBusinessLayer->rename(3, 'bogus', '');

		$this->assertEquals('bogus', $folder->getName());		
	}


	public function testRenameThrowsExWhenFolderNameExists(){
		$folderName = 'hihi';
		$rows = array(
			array('id' => 1)
		);
		
		$trans = $this->getMock('Trans', array('t'));
		$trans->expects($this->once())
			->method('t');
		$this->api->expects($this->once())
			->method('getTrans')
			->will($this->returnValue($trans));
		$this->folderMapper->expects($this->once())
			->method('findByName')
			->with($this->equalTo($folderName))
			->will($this->returnValue($rows));

		$this->setExpectedException('\OCA\News\BusinessLayer\BusinessLayerException');
		$result = $this->folderBusinessLayer->rename(3, $folderName, 'john');
	}


}
