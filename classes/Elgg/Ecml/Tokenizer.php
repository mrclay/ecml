<?php

namespace Elgg\Ecml;

use Elgg\Ecml\Token;
use MrClay\Elgg\Strings;

/**
 * Generate a list of ECML tokens
 *
 * @access private
 */
class Tokenizer {

	const TAG_REGEX = '/\\[([a-z0-9\\.]+)([^\\]]+)?\\]/';
	const ATTR_SEPARATOR = ' ';
	const ATTR_OPERATOR = '=';
	const DELIMITER = 'NWMwYjc0ZjhiYTBjYmE2NzgwMmFkZTQzNmYyZDcxMWY3NGFjMDI1ZA';

	/**
	 * @var Token[]
	 */
	protected $ecmlTokens;

	/**
	 * @var Strings
	 */
	protected $strings;

	public function __construct(Strings $strings) {
		$this->strings = $strings;
	}

	/**
	 * @param string $text
	 * @return Token[] array of ECML tokens
	 */
	public function getTokens($text) {
		$this->ecmlTokens = array();

		$text = preg_replace_callback(Tokenizer::TAG_REGEX, array($this, 'replaceMatch'), $text);
		$pieces = explode(Tokenizer::DELIMITER, $text);

		$tokens = array();
		$last = count($pieces) - 1;
		foreach ($pieces as $i => $piece) {
			if ($piece !== '') {
				$tokens[] = Token::factory($piece);
			}
			if ($i !== $last) {
				$tokens[] = $this->ecmlTokens[$i];
			}
		}
		$this->ecmlTokens = array();
		return $tokens;
	}

	/**
	 * Render an ECML tag
	 *
	 * @param array $matches Array of string matches for a particular tag
	 * @return string
	 */
	protected function replaceMatch($matches) {
		// matches = [full tag, keyword, attributes]
		$token = Token::factory($matches[0], $matches[1], $this->tokenizeAttributes($matches[2]));

		$this->ecmlTokens[] = $token;

		return Tokenizer::DELIMITER;
	}

	/**
	 * Tokenize the ECML tag attributes
	 *
	 * @param string $string Attribute string
	 * @return array
	 */
	protected function tokenizeAttributes($string) {

		$string = trim($string);
		if (empty($string)) {
			return array();
		}

		$attributes = array();
		$pos = 0;
		$char = $this->strings->substr($string, $pos, 1);

		// working var for assembling name and values
		$operand = $name = '';

		while ($char !== false && $char !== '') {
			switch ($char) {
				// handle quoted names/values
				case '"':
				case "'":
					$quote = $char;

					$next_char = $this->strings->substr($string, ++$pos, 1);
					while ($next_char != $quote) {
						// mb_substr returns "" instead of false...
						if ($next_char === false || $next_char === '') {
							// no matching quote. bail.
							return array();
						} elseif ($next_char === '\\') {
							// allow escaping quotes
							$after_escape = $this->strings->substr($string, $pos + 1, 1);
							if ($after_escape === $quote) {
								$operand .= $quote;
								$pos += 2; // skip escape and quote
								$next_char = $this->strings->substr($string, $pos, 1);
								continue;
							}
						}
						$operand .= $next_char;
						$next_char = $this->strings->substr($string, ++$pos, 1);
					}
					break;

				case self::ATTR_SEPARATOR:
					// normalize true and false
					if ($operand == 'true') {
						$operand = true;
					} elseif ($operand == 'false') {
						$operand = false;
					}
					if ($name !== '') {
						$attributes[$name] = $operand;
						$operand = $name = '';
					} elseif ($operand !== '') {
						// boolean attribute (no value)
						$attributes[$operand] = true;
						$operand = '';
					}

					break;

				case self::ATTR_OPERATOR:
					// save name, switch to value
					$name = $operand;
					$operand = '';
					break;

				default:
					$operand .= $char;
					break;
			}

			$char = $this->strings->substr($string, ++$pos, 1);
		}

		// need to get the last attr
		if ($name && $operand) {
			if ($operand == 'true') {
				$operand = true;
			} else if ($operand == 'false') {
				$operand = false;
			}
			$attributes[$name] = $operand;
		}

		return $attributes;
	}
}
