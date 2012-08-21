<?php

namespace MrClay\Elgg;

class Strings {

	/**
	 * @param string $str
	 * @param int $start
	 * @param int|null $length
	 * @return string
	 */
	public function substr($str, $start, $length = null) {
		if (is_callable('mb_substr')) {
			return mb_substr($str, $start, $length, 'UTF-8');
		}
		return substr($str, $start, $length);
	}
}
