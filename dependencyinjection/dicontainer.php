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

namespace OCA\News\DependencyInjection;

use OCA\AppFramework\DependencyInjection\DIContainer as BaseContainer;

use OCA\News\Controller\PageController;
use OCA\News\Controller\FolderController;
use OCA\News\Controller\FeedController;
use OCA\News\Controller\ItemController;
use OCA\News\Controller\ExportController;
use OCA\News\Controller\UserSettingsController;

use OCA\News\BusinessLayer\FolderBusinessLayer;
use OCA\News\BusinessLayer\FeedBusinessLayer;
use OCA\News\BusinessLayer\ItemBusinessLayer;

use OCA\News\Db\FolderMapper;
use OCA\News\Db\FeedMapper;
use OCA\News\Db\ItemMapper;
use OCA\News\Db\StatusFlag;

use OCA\News\Utility\Fetcher;
use OCA\News\Utility\FeedFetcher;
use OCA\News\Utility\TwitterFetcher;
use OCA\News\Utility\OPMLExporter;


class DIContainer extends BaseContainer {


	/**
	 * Define your dependencies in here
	 */
	public function __construct(){
		// tell parent container about the app name
		parent::__construct('news');

		/**
		 * Configuration values
		 */
		$this['autoPurgeCount'] = 200;
		$this['simplePieCacheDirectory'] = __DIR__ . '/../cache/simplepie/';
		$this['simplePieCacheDuration'] = 1000;  // seconds


		/** 
		 * CONTROLLERS
		 */
		$this['PageController'] = $this->share(function($c){
			return new PageController($c['API'], $c['Request']);
		});

		$this['FolderController'] = $this->share(function($c){
			return new FolderController($c['API'], $c['Request'], 
				$c['FolderBusinessLayer']);
		});

		$this['FeedController'] = $this->share(function($c){
			return new FeedController($c['API'], $c['Request'], 
				$c['FeedBusinessLayer'], $c['FolderBusinessLayer']);
		});

		$this['ItemController'] = $this->share(function($c){
			return new ItemController($c['API'], $c['Request'], 
				$c['ItemBusinessLayer'], $c['FeedBusinessLayer']);
		});

		$this['ExportController'] = $this->share(function($c){
			return new ExportController($c['API'], $c['Request'], 
			                            $c['FeedBusinessLayer'],
				                        $c['FolderBusinessLayer'], 
				                        $c['OPMLExporter']);
		});

		$this['UserSettingsController'] = $this->share(function($c){
			return new UserSettingsController($c['API'], $c['Request'], 
					$c['ItemBusinessLayer']);
		});


		/**
		 * Business Layer
		 */
		$this['FolderBusinessLayer'] = $this->share(function($c){
			return new FolderBusinessLayer($c['FolderMapper'], $c['API']);
		});

		$this['FeedBusinessLayer'] = $this->share(function($c){
			return new FeedBusinessLayer($c['FeedMapper'], $c['Fetcher'],
								$c['ItemMapper'], $c['API'], $c['TimeFactory']);
		});

		$this['ItemBusinessLayer'] = $this->share(function($c){
			return new ItemBusinessLayer($c['ItemMapper'], $c['StatusFlag'],
								$c['TimeFactory'], $c['autoPurgeCount']);
		});


		/**
		 * MAPPERS
		 */
		$this['FolderMapper'] = $this->share(function($c){
			return new FolderMapper($c['API']);
		});

		$this['FeedMapper'] = $this->share(function($c){
			return new FeedMapper($c['API']);
		});

		$this['ItemMapper'] = $this->share(function($c){
			return new ItemMapper($c['API']);
		});


		/**
		 * Utility
		 */
		$this['Fetcher'] = $this->share(function($c){
			$fetcher = new Fetcher();

			// register fetchers in order
			// the most generic fetcher should be the last one
			$fetcher->registerFetcher($c['TwitterFetcher']); // twitter timeline
			$fetcher->registerFetcher($c['FeedFetcher']);

			return $fetcher;
		});

		$this['FeedFetcher'] = $this->share(function($c){
			return new FeedFetcher(
				$c['API'], 
				$c['SimplePieAPIFactory'],
				$c['FaviconFetcher'],
				$c['TimeFactory'],
				$c['simplePieCacheDirectory'],
				$c['simplePieCacheDuration']);
		});

		$this['TwitterFetcher'] = $this->share(function($c){
			return new TwitterFetcher($c['FeedFetcher']);
		});

		$this['StatusFlag'] = $this->share(function($c){
			return new StatusFlag();
		});

		$this['OPMLExporter'] = $this->share(function($c){
			return new OPMLExporter();
		});

	}
}

