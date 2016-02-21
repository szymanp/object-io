<?php
namespace Szyman\ObjectIO;

use Light\ObjectAccess\TestData\Author;
use Light\ObjectAccess\TestData\Setup;
use Light\ObjectAccess\Type\TypeRegistry;

class ObjectIOTest extends \PHPUnit_Framework_TestCase
{
	/** @var TypeRegistry */
	private $typeRegistry;
	/** @var ObjectIO */
	private $io;

	public function setUp()
	{
		$this->typeRegistry = Setup::create()->getTypeRegistry();
		$this->io = new ObjectIO($this->typeRegistry);
	}

	public function testReadWithObjects()
	{
		$target = new Author();

		$data = new \stdClass();
		$data->name = "James Bond";
		$data->age = 42;

		$this->io->read($data, $target);

		$this->assertEquals($target->age, $data->age);
		$this->assertEquals($target->name, $data->name);
	}

	public function testWrite()
	{
		$target = new Author();
		$target->age = 42;
		$target->id = 5;
		$target->name = "James Bond";
		$target->whatever = "hello";

		$data = $this->io->write($target);
	}
}
