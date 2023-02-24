<?php

namespace CubaDevOps\Skeleton\Domain;

use ArrayObject;
use CubaDevOps\Skeleton\Domain\ValueObjects\FormField;

class FormFieldsDefinition
{
    /**
     * @var FormField[]
     */
    private $fields;
    /**
     * @var string
     */
    private $form_id;

    public function __construct(string $form_id)
    {
        $this->fields = new ArrayObject();
        $this->form_id = $form_id;
    }

    /**
     * @return array|FormField[]
     */
    public function getFields(): array
    {
        return $this->fields->getArrayCopy();
    }

    public function addField(FormField $field): self
    {
        $this->fields->offsetSet($field->getName(),$field);
        return $this;
    }

    public function updateField(string $field_name,$value): self
    {
        if ($this->fields->offsetExists($field_name)){
            $this->fields->offsetGet($field_name)->setValue($value);
        }
        return $this;
    }

    public function removeField(FormField $field): self
    {
        $this->fields->offsetUnset($field->getName());
        return $this;
    }

    public function getField(string $field_name)
    {
        return $this->fields->offsetGet($field_name);
    }

    /**
     * @return string
     */
    public function getFormId(): string
    {
        return $this->form_id;
    }

    public function toArray(): array
    {
        $data = [];
        foreach ($this->fields->getIterator() as $item){
            $data[$item->getName()] = $item->getValue();
        }
        return $data;
    }
}