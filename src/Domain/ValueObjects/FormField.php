<?php

namespace CubaDevOps\Skeleton\Domain\ValueObjects;

use Exception;

class FormField
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var mixed
     */
    private $value;
    /**
     * @var bool
     */
    private $multilang;

    public function __construct(string $name,$value,bool $multilang = false)
    {
        $this->name = $name;
        $this->multilang = $multilang;
        $this->setValue($value);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isMultilang(): bool
    {
        return $this->multilang;
    }

    /**
     * @param mixed $value
     * @throws Exception
     */
    public function setValue($value)
    {
        if ($this->multilang && !is_array($value)){
            throw new Exception('An array was expected if the field is multilanguage');
        }
        $this->value = $value;
    }
}