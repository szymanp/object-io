<?php
namespace Szyman\ObjectIO;

use Light\Exception\InvalidParameterType;
use Light\ObjectAccess\Exception\TypeException;
use Light\ObjectAccess\Resource\Origin;
use Light\ObjectAccess\Resource\ResolvedObject;
use Light\ObjectAccess\Resource\ResolvedValue;
use Light\ObjectAccess\Resource\Util\EmptyResourceAddress;
use Light\ObjectAccess\Transaction\Util\DummyTransaction;
use Light\ObjectAccess\Type\Complex\CanonicalAddress;
use Light\ObjectAccess\Type\ComplexTypeHelper;
use Light\ObjectAccess\Type\TypeRegistry;

class ObjectIO
{
	/** @var TypeRegistry */
	protected $typeRegistry;

	public function __construct(TypeRegistry $typeRegistry)
	{
		$this->typeRegistry = $typeRegistry;
	}

	/**
	 * Modifies the target object based on the input data.
	 * @param object	$data
	 * @param object	$target
	 * @throws \Light\ObjectAccess\Exception\TypeException
	 */
	public function read($data, $target)
	{
		$typeHelper = $this->getTypeHelper($target);
		$resource = $this->getResource($typeHelper, $target);

		foreach($data as $key => $value)
		{
			if (!is_scalar($value))
			{
				throw new TypeException("Only scalar values are supported for property %1", $key);
			}
			$typeHelper->writeProperty($resource, $key, $value, new DummyTransaction());
		}
	}

	public function write($target)
	{
		$result = new \stdClass;

		$typeHelper = $this->getTypeHelper($target);
		$resource = $this->getResource($typeHelper, $target);

		$properties = $typeHelper->getType()->getProperties();
		foreach($properties as $property)
		{
			if ($property->isReadable())
			{
				$name = $property->getName();
				$value = $typeHelper->readProperty($resource, $name);
				if ($value instanceof ResolvedValue)
				{
					$result->{$name} = $value->getValue();
				}
			}
		}

		return $result;
	}

	/**
	 * Returns the URL at which the given object can be accessed.
	 *
	 * This method always returns the canonical URL. Therefore, the ComplexType must implement the CanonicalAddress
	 * interface.
	 *
	 * @param object	$target
	 * @return string	An URL for the object, if found; otherwise, NULL.
	 */
	public function getUrl($target)
	{
		$typeHelper = $this->getTypeHelper($target);
		$type = $typeHelper->getType();

		if ($type instanceof CanonicalAddress)
		{
			$address = $type->getCanonicalAddress($target);
			if ($address->hasStringForm())
			{
				return $address->getAsString();
			}
		}

		return null;
	}

	/**
	 * Returns a complex type helper for the given object.
	 * @param $target
	 * @return \Light\ObjectAccess\Type\ComplexTypeHelper
	 * @throws TypeException
	 */
	private function getTypeHelper($target)
	{
		$typeHelper = $this->typeRegistry->getTypeHelperByValue($target);
		if (!($typeHelper instanceof ComplexTypeHelper))
		{
			throw new InvalidParameterType('$target', $target, "object");
		}
		return $typeHelper;
	}

	/**
	 * Returns a resource object encapsulating the PHP object.
	 * @param ComplexTypeHelper $typeHelper
	 * @param object            $phpObject
	 * @return ResolvedObject
	 */
	private function getResource(ComplexTypeHelper $typeHelper, $phpObject)
	{
		return new ResolvedObject($typeHelper, $phpObject, EmptyResourceAddress::create(), Origin::unavailable());
	}
}