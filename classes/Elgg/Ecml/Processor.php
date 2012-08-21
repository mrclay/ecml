<?php

namespace Elgg\Ecml;

use Elgg\Ecml\Tokenizer;
use Elgg\Ecml\Token;
use MrClay\Elgg;
use Exception;

/**
 * Turn ECML markup into text via plugin hooks
 *
 * @access private
 */
class Processor {

	/**
	 * @var Tokenizer
	 */
	protected $tokenizer;

	/**
	 * @var Elgg
	 */
	protected $elgg;

	/**
	 * @param Tokenizer $tokenizer
	 * @param Elgg $elgg
	 */
	public function __construct(Tokenizer $tokenizer, Elgg $elgg) {
		$this->tokenizer = $tokenizer;
		$this->elgg = $elgg;
	}

	/**
	 * @param string $text
	 * @param array $context info to pass to the plugin hooks
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	public function process($text, $context = array()) {
		$tokens = $this->tokenizer->getTokens($text);

		// allow processors that might need to see all the tokens at once
		$this->elgg->events->triggerHook("ecml:preprocess", "token_list", $context, $tokens);
		if (!is_array($tokens)) {
			throw new Exception($this->elgg->_('ecml:Exception:InvalidTokenList'));
		}

		// process tokens in isolation
		$output = '';
		foreach ($tokens as $token) {
			if (is_string($token)) {
				$output .= $token;
			} elseif ($token instanceof Token) {
				/* @var Token $token */
				if ($token->isText) {
					$output .= $token->content;
				} else {
					$output .= $this->elgg->events->triggerHook("ecml:replace", "token", $context, $token);
				}
			} else {
				throw new Exception($this->elgg->_('ecml:Exception:InvalidToken'));
			}
		}
		return $output;
	}
}
