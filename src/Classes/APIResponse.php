<?php

namespace App\Classes;

use JMS\Serializer\Annotation as Serializer;
use Exception;
use App\Classes\DataPoly;


class APIResponse 
{

    const ERR_NOT_VALID_TYPE = "Error. Type not valid ";

    /**
     *
     * @var boolean
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"all", "person","player","coach","club"})
     */
    public $error;

    /**
     *
     * @var entity
     * @Serializer\Type("bool")
     * @Serializer\Groups({"all", "person","player","coach","club"})
     */
    public $dataBool;

    /**
     *
     * @var integer
     * @Serializer\Type("integer")
     * @Serializer\Groups({"all", "person","player","coach","club"})
     */
    public $dataInt;

    /**
     *
     * @var array
     * @Serializer\Type("array<App\Classes\DataPoly>")
     * @Serializer\Groups({"all", "person","player","coach","club"})
     */
    public $dataArray;

    /**
     *
     * @var entity
     * @Serializer\Type("App\Classes\DataPoly")
     * @Serializer\Groups({"all", "person","player","coach","club"})
     */
    public $dataPoly;

    /**
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\Groups({"all", "person","player","coach","club"})
     */
    public $dataString;

    /**
     * Data can only be set in constructor.
     * All the setters are private methods
     *
     * @param bool $error
     * @param type $data
     */
    public function __construct(bool $error = false, $data = null)
    {
        $this->setError($error);
        
        if (is_object($data)) {
            $this->setDataPoly($data);
        } elseif (is_array($data)) {
            $this->setDataArray($data);
        } elseif (is_int($data)) {
            $this->setDataInt($data);
        } elseif (is_bool($data)) {
            $this->setDataBool($data);
        } elseif (is_string($data)) {
            $this->setDataString($data);
        } elseif (is_null($data)) {
            $this->setDataBool($data);
        } else {
            $this->setError(true);
            throw new Exception(self::ERR_NOT_VALID_TYPE . " {$data}");
        }
    }

    private function setError(bool $error): self
    {
        $this->error = $error;
        return $this;
    }

    public function getError(): bool
    {
        return $this->error;
    }

    private function setDataBool($dataBool): self
    {
        $this->dataBool = $dataBool;
        return $this;
    }

    public function getDataBool()
    {
        return $this->dataBool;
    }

    private function setDataInt($dataInt): self
    {
        $this->dataInt = $dataInt;
        return $this;
    }

    public function getDataInt()
    {
        return $this->dataInt;
    }

    private function setDataArray(array $dataArray): self
    {
        $this->dataArray = $dataArray;
        return $this;
    }

    public function getDataArray()
    {
        return $this->dataArray;
    }

    private function setDataPoly(DataPoly $dataPoly): self
    {
        $this->dataPoly = $dataPoly;
        return $this;
    }

    public function getDataPoly()
    {
        return $this->dataPoly;
    }

    private function setDataString($dataString): self
    {
        $this->dataString = $dataString;
        return $this;
    }

    public function getDataString()
    {
        return $this->dataString;
    }

    public function isEmpty()
    {
        return
                $this->isDataBoolEmpty() &&
                $this->isDataIntEmpty() &&
                $this->isDataStringEmpty() &&
                $this->isDataPolyEmpty() &&
                $this->isDataArrayEmpty();
    }

    private function isDataBoolEmpty()
    {

        return !is_bool($this->getDataBool());
    }

    private function isDataIntEmpty()
    {
        return !is_int($this->getDataInt());
    }

    private function isDataStringEmpty()
    {
        return !is_string($this->getDataString());
    }

    private function isDataPolyEmpty()
    {
        return !is_object($this->getDataPoly());
    }

    private function isDataArrayEmpty()
    {
        return !is_array($this->getDataArray());
    }

    /**
     * Gets the object data
     *
     * @return type
     */
    public function getData()
    {
        if (!$this->isDataBoolEmpty()) {
            $r = $this->getDataBool();
        } else if (!$this->isDataIntEmpty()) {
            $r = $this->getDataInt();
        } else if (!$this->isDataStringEmpty()) {
            $r = $this->getDataString();
        } else if (!$this->isDataPolyEmpty()) {
            $r = $this->getDataPoly();
        } else if (!$this->isDataArrayEmpty()) {
            $r = $this->getDataArray();
        } else {
            $r = null;
        }

        return $r;
    }

}
