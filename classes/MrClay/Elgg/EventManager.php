<?php

namespace MrClay\Elgg;

/**
 * Wrapper for Native Elgg events for unit testing
 */
class EventManager {

	public function triggerEvent($event, $objectType, $object = null) {
		return elgg_trigger_event($event, $objectType, $object);
	}

	public function triggerHook($hook, $type, $params = null, $returnValue = null) {
		return elgg_trigger_plugin_hook($hook, $type, $params, $returnValue);
	}
}
