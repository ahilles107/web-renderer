<?php

namespace SWP\TemplateEngineBundle\Gimme\Meta;

use Symfony\Component\Yaml\Parser;

class Meta
{
    /**
     * Configuration definition for current Meta
     *
     * @var array
     */
    protected $configuration;

    /**
     * Original Meta values (json|array)
     *
     * @var json|array
     */
    protected $values;

    /**
     * Create Meta class from provided configuration and values
     *
     * @param string       $configuration
     * @param string|array $values
     */
    public function __construct($configuration, $values)
    {
        if (!is_readable($configuration)){
            throw new \InvalidArgumentException("Configuration file is not readable for parser");
        }

        $yaml = new Parser();
        $this->configuration = array_slice($yaml->parse(file_get_contents($configuration)), 0, 1);
        $this->configuration = array_shift($this->configuration);
        $this->values = $values;

        switch ($values) {
            case is_array($values):
                $this->fillFromArray($values);
                break;

            case $this->isJson($values):
                $this->fillFromJson($values);
                break;
        }
    }

    /**
     * Fill Meta from array. Array must have property names and keys.
     * 
     * @param array $values Array with propery names as keys
     *
     * @return bool
     */
    private function fillFromArray(array $values)
    {   
        foreach ($this->getExposedProperties($values) as $key => $propertyValue) {
                $this->$key = $propertyValue;
                $valuesArray[$key] = $propertyValue;
        }

        return true;
    }

    /**
     * Fill Meta class from json values
     *
     * @param string $values
     *
     * @return array
     */
    private function fillFromJson($values)
    {
        $this->fillFromArray(json_decode($values, true));

        return true;
    }

    /**
     * Check if string is JSON
     *
     * @param  string
     *
     * @return bool
     */
    private function isJson($string)
    {
        json_decode($string);

        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Get exposed properties (acording to configuration) from provided values
     *
     * @return array
     */
    private function getExposedProperties(array $values = array())
    {
        if (count($values) === 0) {
            $values = $this->values;
        }

        $exposedProperties = [];
        if (count($values) > 0) {
            foreach ($values as $key => $propertyValue) {
                if (array_key_exists($key, $this->configuration['properties'])) {
                    $exposedProperties[$key] = $propertyValue;
                }
            }
        }

        return $exposedProperties;
    }

    /**
     * Use to_string property from configuration if provided, json with exposed properties otherwise
     * 
     * @return string
     */
    public function __toString()
    {
        if (array_key_exists('to_string', $this->configuration)) {
            $toStringProperty = $this->configuration['to_string'];

            if (isset($this->$toStringProperty)) {
                return $this->$toStringProperty;
            }
        }

        return json_encode($this->getExposedProperties());
    }
}