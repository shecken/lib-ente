<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

declare(strict_types=1);

namespace CaT\Ente\ILIAS;

use CaT\Ente\Component;
use CaT\Ente\Entity AS IEntity;

/**
 * Implementation of a provider for ILIAS.
 */
final class Provider implements \CaT\Ente\Provider
{
	/**
	 * @var \ilObject
	 */
	private $object;

	/**
	 * @var Entity
	 */
	private $entity;

	/**
	 * @var UnboundProvider
	 */
	private $unbound_provider;

	/**
	 * @var array<string,Component>
	 */
	private $components;

	final public function __construct(\ilObject $object, UnboundProvider $unbound_provider)
	{
		$this->object = $object;
		$this->entity = new Entity($object);
		$this->unbound_provider = $unbound_provider;
		$this->components = [];
	}

	/**
	 * @inheritdocs
	 */
	final public function componentTypes() : array
	{
		return $this->unbound_provider->componentTypes();
	}

	/**
	 * @inheritdocs
	 */
	final public function componentsOfType(string $component_type) : array
	{
		if (isset($this->components[$component_type])) {
			return $this->components[$component_type];
		}

		$components = $this->unbound_provider->buildComponentsOf($component_type, $this->entity());
		$this->checkComponentArray($components, $component_type);
		$this->components[$component_type] = $components;
		return $components;
	}

	/**
	 * @inheritdocs
	 */
	final public function entity() : IEntity
	{
		return $this->entity;
	}

	/**
	 * Get the entity object of the component.
	 *
	 * @return \ilObject
	 */
	final public function object() : \ilObject
	{
		return $this->object;
	}

	/**
	 * Get the owner object of the component.
	 *
	 * @return  \ilObject[]
	 */
	final public function owners() : array
	{
		return $this->unbound_provider->owners();
	}

	/**
	 * Get the unbound provider underlying this.
	 *
	 * @return  UnboundProvider
	 */
	final public function unboundProvider() : UnboundProvider
	{
		return $this->unbound_provider;
	}

	/**
	 * Checks if the $var is a valid component array for the given type.
	 * @param   mixed   $var
	 */
	private function checkComponentArray($var, string $component_type)
	{
		if (!is_array($var)) {
			throw new \UnexpectedValueException(
				"Expected buildComponentsOf to return an array, got ".gettype($var));
		}

		foreach($var as $component) {
			if (!($component instanceof $component_type)) {
				throw new \UnexpectedValueException(
					"Expected build components to have the type $component_type, got "
					.get_class($component)
				);
			}
			if (!$component->entity() === $this->entity()) {
				throw new \UnexpectedValueException(
					'Expected build components to have the same entity as $this.');
			}
		}
	}
}
