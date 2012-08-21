<?php

/*
 * Copyright (c) 2009 Fabien Potencier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace MrClay;

use InvalidArgumentException;
use Closure;

/**
 * Non-ArrayAccess version of Pimple
 *
 * @author  Fabien Potencier
 * @author  Steve Clay
 */
class DiContainer
{
	private $values;

	/**
	 * Instantiate the container.
	 *
	 * @param array $config array with keys "params", "services", and "sharedServices"
	 *                      each being an array of ID to value
	 */
	public function __construct (array $config = array())
	{
		if (! empty($config['params'])) {
			foreach ($config['params'] as $id => $value) {
				$this->setParam($id, $value);
			}
		}
		if (! empty($config['services'])) {
			foreach ($config['services'] as $id => $value) {
				$this->setService($id, $value);
			}
		}
		if (! empty($config['sharedServices'])) {
			foreach ($config['sharedServices'] as $id => $value) {
				$this->setSharedService($id, $value);
			}
		}
	}

	/**
	 * Sets a parameter
	 *
	 * @param string $id The unique identifier for the parameter
	 * @param mixed $value The value of the parameter
	 *
	 * @return DiContainer
	 */
	public function setParam($id, $value)
	{
		if ($value instanceof Closure) {
			$value = function ($c) use ($value) {
				return $value;
			};
		}
		$this->values[$id] = $value;
		return $this;
	}

	/**
	 * Sets a service
	 *
	 * @param string $id The unique identifier for the service
	 * @param Closure $callable The function that returns the service
	 *
	 * @return DiContainer
	 */
	public function setService($id, Closure $callable)
	{
		$this->values[$id] = $callable;
		return $this;
	}

	/**
	 * Sets an object for use as a shared resource
	 *
	 * Internally sets a closure that stores the result of the given closure for
	 * uniqueness in the scope of this instance of Pimple.
	 *
	 * @param string $id The unique identifier for the object
	 * @param Closure $callable The function that returns the service
	 *
	 * @return DiContainer
	 */
	public function setSharedService($id, Closure $callable)
	{
		$this->values[$id] = function ($c) use ($callable) {
			static $object;

			if (null === $object) {
				$object = $callable($c);
			}

			return $object;
		};
		return $this;
	}

	/**
	 * Gets a parameter or an object.
	 *
	 * @param string $id The unique identifier for the parameter or object
	 *
	 * @return mixed The value of the parameter or an object
	 *
	 * @throws InvalidArgumentException if the identifier is not defined
	 */
	public function get($id)
	{
		if (!array_key_exists($id, $this->values)) {
			throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
		}
		$value = $this->values[$id];
		if ($value instanceof Closure) {
			/* @var Closure $value */
			return $value($this);
		}
		return $value;
	}

	/**
	 * Checks if a parameter or an object is set.
	 *
	 * @param string $id The unique identifier for the parameter or object
	 *
	 * @return Boolean
	 */
	public function has($id)
	{
		return array_key_exists($id, $this->values);
	}

	/**
	 * Removes a parameter or an object.
	 *
	 * @param string $id The unique identifier for the parameter or object
	 */
	public function remove($id)
	{
		unset($this->values[$id]);
	}

	/**
	 * Gets a parameter or the closure defining an object.
	 *
	 * @param string $id The unique identifier for the parameter or object
	 *
	 * @return mixed The value of the parameter or the closure defining an object
	 *
	 * @throws InvalidArgumentException if the identifier is not defined
	 */
	public function getRawValue($id)
	{
		if (!array_key_exists($id, $this->values)) {
			throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
		}

		return $this->values[$id];
	}

	/**
	 * Extends an object definition.
	 *
	 * Useful when you want to extend an existing object definition,
	 * without necessarily loading that object.
	 *
	 * @param string  $id       The unique identifier for the object
	 * @param Closure $callable A closure to extend the original
	 *
	 * @return Closure The wrapped closure
	 *
	 * @throws InvalidArgumentException if the identifier is not defined
	 */
	public function extendService($id, Closure $callable)
	{
		if (!array_key_exists($id, $this->values)) {
			throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
		}

		$factory = $this->values[$id];

		if (!($factory instanceof Closure)) {
			throw new InvalidArgumentException(sprintf('Identifier "%s" does not contain an object definition.', $id));
		}
		/* @var Closure $factory */

		return $this->values[$id] = function ($c) use ($callable, $factory) {
			return $callable($factory($c), $c);
		};
	}

	/**
	 * Returns all defined value names.
	 *
	 * @return array An array of value names
	 */
	public function getIds()
	{
		return array_keys($this->values);
	}
}
