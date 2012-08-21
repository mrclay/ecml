<?php

namespace Elgg\Ecml;

use MrClay\Elgg;

/**
 * Provides the ECML service.
 *
 * @package ECML
 */

// be sure to run after other plugins
elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init', 9999);

function init() {
	// for 1.8
	if (!class_exists('ElggAutoloadManager')) {
		spl_autoload_register(function ($class) {
			$file = __DIR__ . '/classes/' . strtr($class, '_\\', '//') . '.php';
			is_file($file) && (require $file);
		});
	}

	elgg_register_plugin_hook_handler('unit_test', 'system', __NAMESPACE__ . '\\unit_test');

	$elgg = elgg();

	$elgg->getDi()->setSharedService('ecml_processor', function () use (&$elgg) {
		/* @var Elgg $elgg */
		$tokenizer = new Tokenizer($elgg->strings);
		return new Processor($tokenizer, $elgg);
	});

	// get list of views to process for ECML
	// entries should be of the form 'view/name' => 'View description'
	$default_views = array(
		'output/longtext' => $elgg->_('ecml:view:output_longtext'),
	);
	$views = $elgg->events->triggerHook('get_views', 'ecml', null, $default_views);

	foreach ($views as $view => $desc) {
		$elgg->events->triggerHook('view', $view, __NAMESPACE__ . '\\process_view');
	}
}

/**
 * @return Elgg
 */
function elgg() {
	static $inst;
	if (null === $inst) {
		$inst = new Elgg();
	}
	return $inst;
}

/**
 * Processes a view output for ECML tags
 *
 * @param string $hook   The name of the hook
 * @param string $name   The name of the view
 * @param string $value  The value of the view
 * @param array  $params The parameters for the view
 * @return string
 */
function process_view($hook, $name, $value, $params) {
	$processor = elgg()->ecml_processor;
	/* @var Processor $processor */
	return $processor->process($value, array(
		'view' => $name,
		'view_params' => $params,
	));
}

function unit_test($hook, $type, $value, $params) {
	spl_autoload_register(function ($class) {
		if (0 === strpos($class, 'EcmlTests\\')) {
			$file = __DIR__ . '/tests/' . strtr($class, '_\\', '//') . '.php';
			is_file($file) && (require $file);
		}
	});
	$path = __DIR__ . '/tests/EcmlTests';

	error_reporting(E_ALL);
	$value[] = "$path/TokenizerTest.php";

	return $value;
}
