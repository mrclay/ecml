<?php

namespace EcmlTests;

use MrClay\Elgg;

class Base extends \UnitTestCase {

	public function getElgg() {
		return new Elgg();
	}
}
