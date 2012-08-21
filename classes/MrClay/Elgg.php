<?php

namespace MrClay;

use MrClay\DiContainer;
use MrClay\Elgg\EventManager;
use MrClay\Elgg\Strings;

/**
 * Demonstration of a core service provider wrapping a DI container
 *
 * @property-read EventManager $events
 * @property-read Strings $strings
 */
class Elgg {
	/**
	 * @var DiContainer
	 */
	protected $di;

	/**
	 * @param DiContainer $di
	 */
	public function __construct(DiContainer $di = null) {
		if (!$di) {
			$di = new DiContainer();
		}

		$di->setSharedService('events', function (DiContainer $di) {
			return new EventManager();
		});
		$di->setSharedService('strings', function (DiContainer $di) {
			return new Strings();
		});

		$this->di = $di;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name) {
		return $this->di->get($name);
	}

	/**
	 * @return DiContainer
	 */
	public function getDi() {
		return $this->di;
	}

	/**
	 * @param string $message_key
	 * @param array $args
	 * @param string $language
	 * @return string
	 */
	public function _($message_key, $args = array(), $language = "")
	{
		return elgg_echo($message_key, $args, $language);
	}
}
