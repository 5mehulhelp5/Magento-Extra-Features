<?php

namespace Codilar\Migration\Model;

use Migration\Handler\SetValue;
use Migration\ResourceModel\Record;

class SetValueTypeCast extends SetValue
{

    protected $value;
    public function __construct($value)
    {
        parent::__construct($value);
    }

    /**
     * @param Record $recordToHandle
     * @param Record $oppositeRecord
     * @return void
     * @throws \Migration\Exception
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $valueStored = $recordToHandle->getValue($this->field);
        $operator = substr((string)$this->value, 0, 1);
        $value = substr((string)$this->value, 1);
        switch ($operator) {
            case '+':
                $value = $valueStored + $value;
                break;
            case '-':
                $value = $valueStored - $value;
                break;
            default:
                $value = $this->value;
        }
        $recordToHandle->setValue($this->field, $value);
    }
}
