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

namespace OCA\News\Controller;

use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Core\API;
use \OCA\AppFramework\Http\Request;

use \OCA\News\BusinessLayer\FolderBusinessLayer;
use \OCA\News\BusinessLayer\BusinessLayerException;


class FolderController extends Controller {

	private $folderBusinessLayer;

	public function __construct(API $api, Request $request, 
	                            FolderBusinessLayer $folderBusinessLayer){
		parent::__construct($api, $request);
		$this->folderBusinessLayer = $folderBusinessLayer;
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function folders(){
		$folders = $this->folderBusinessLayer->findAll($this->api->getUserId());
		$result = array(
			'folders' => $folders
		);
		return $this->renderJSON($result);
	}


	private function setOpened($isOpened){
		$userId = $this->api->getUserId();
		$folderId = (int) $this->params('folderId');

		$this->folderBusinessLayer->open($folderId, $isOpened, $userId);
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function open(){
		$this->setOpened(true);
		return $this->renderJSON();
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function collapse(){
		$this->setOpened(false);
		return $this->renderJSON();
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function create(){
		$userId = $this->api->getUserId();
		$folderName = $this->params('folderName');

		try {
			$folder = $this->folderBusinessLayer->create($folderName, $userId);

			$params = array(
				'folders' => array($folder)
			);
			return $this->renderJSON($params);

		} catch (BusinessLayerException $ex){

			return $this->renderJSON(array(), $ex->getMessage());
		}
		
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function delete(){
		$userId = $this->api->getUserId();
		$folderId = (int) $this->params('folderId');

		$this->folderBusinessLayer->delete($folderId, $userId);

		return $this->renderJSON();
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function rename(){
		$userId = $this->api->getUserId();
		$folderName = $this->params('folderName');
		$folderId = (int) $this->params('folderId');

		try {
			$folder = $this->folderBusinessLayer->rename($folderId, $folderName, $userId);

			$params = array(
				'folders' => array($folder)
			);
			return $this->renderJSON($params);

		} catch (BusinessLayerException $ex){

			return $this->renderJSON(array(), $ex->getMessage());
		}
	}


}