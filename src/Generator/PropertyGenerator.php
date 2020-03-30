<?php
namespace Mty95\Generator;

use Laminas\Code\Generator\Exception;
use Laminas\Code\Generator\TypeGenerator;

class PropertyGenerator extends \Laminas\Code\Generator\PropertyGenerator
{
	/**
	 * @var TypeGenerator|null
	 */
	protected $type = null;

	/**
	 * @var bool
	 */
	private $omitDefaultValue = false;

	public function __construct(
		$name = null,
		$defaultValue = null,
		$type = null,
		$flags = \Laminas\Code\Generator\PropertyGenerator::FLAG_PUBLIC
	)
	{
		\Laminas\Code\Generator\PropertyGenerator::__construct($name, $defaultValue, $flags);

		if (null !== $type)
		{
			$this->setType($type);
		}
	}

	/**
	 * @param  string $type
	 * @return PropertyGenerator
	 */
	public function setType($type)
	{
		$this->type = TypeGenerator::fromTypeString($type);

		return $this;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type
			? (string) $this->type
			: null;
	}

	public function generate()
	{
		$name         = $this->getName();
		$defaultValue = $this->getDefaultValue();

		$output = '';

		if (($docBlock = $this->getDocBlock()) !== null) {
			$docBlock->setIndentation('    ');
			$output .= $docBlock->generate();
		}

		if ($this->isConst()) {
			if ($defaultValue !== null && ! $defaultValue->isValidConstantType()) {
				throw new Exception\RuntimeException(sprintf(
					'The property %s is said to be '
					. 'constant but does not have a valid constant value.',
					$this->name
				));
			}
			$output .= $this->indentation . $this->getVisibility() . ' const ' . $this->generateTypeHint() . $name . ' = '
				. ($defaultValue !== null ? $defaultValue->generate() : 'null;');

			return $output;
		}

		$output .= $this->indentation . $this->getVisibility() . ($this->isStatic() ? ' static ' : ' ') . $this->generateTypeHint() . '$' . $name;

		if ($this->omitDefaultValue) {
			return $output . ';';
		}

		return $output . ' = ' . ($defaultValue !== null ? $defaultValue->generate() : 'null;');
	}

	/**
	 * @return string
	 */
	private function generateTypeHint()
	{
		if (null === $this->type) {
			return '';
		}

		return $this->type->generate() . ' ';
	}
}
