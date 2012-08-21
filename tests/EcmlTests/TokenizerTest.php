<?php

namespace EcmlTests;

use Elgg\Ecml\Token;
use Elgg\Ecml\Tokenizer;

class TokenizerTest extends Base {

	/**
	 * @var Tokenizer
	 */
	protected $tk;

	function setUp() {
		$elgg = $this->getElgg();
		$this->tk = new Tokenizer($elgg->strings);
		parent::setUp();
	}

	function testGetTokens() {
		foreach ($this->getSingleTokenData() as $text => $test) {
			$tokens = $this->tk->getTokens($text);
			$this->assertTrue(isset($tokens[0]));
			$token = $tokens[0];
			$this->assertIsA($token, 'Elgg\\Ecml\\Token');
			/* @var Token $token */
			$this->assertEqual($token->keyword, $test['keyword']);
			$this->assertIdentical($token->attrs, $test['attrs']);
		}
	}

	function getSingleTokenData() {
		return array(
			'[bar foo="234" bool bool2=true f-.2 pow=\'pow\']' => array(
				'keyword' => 'bar',
				'attrs' => array(
					'foo' => '234',
					'bool' => true,
					'bool2' => true,
					'f-.2' => true,
					'pow' => 'pow',
				),
			),
			'[bar.123  cat="fig\\"ht"]' => array(
				'keyword' => 'bar.123',
				'attrs' => array(
					'cat' => 'fig"ht',
				),
			),
			'[foo a="b]' => array(
				'keyword' => 'foo',
				'attrs' => array(),
			),
			'[foo a="b\\"]' => array(
				'keyword' => 'foo',
				'attrs' => array(),
			),
		);
	}
}
