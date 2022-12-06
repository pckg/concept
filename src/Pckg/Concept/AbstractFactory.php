<?php

namespace Pckg\Concept;

use Pckg\Concept\Factory\CreateFromMapper;

class AbstractFactory
{
    use CreateFromMapper {
        CreateFromMapper::create as parentCreate;
    }

    protected $services = [];

    /**
     * @param string $key
     * @param array $params
     *
     * @throws \Exception
     */
    public function create(string $key, $params = [])
    {
        $element = $this->parentCreate($key, $params);

        if (is_array($element)) {
            foreach ($element as $key => $e) {
                $this->applyServicesOnCreation($key, $e);
            }
        } else {
            $this->applyServicesOnCreation($key, $element);
        }

        return $element;
    }

    public function applyServicesOnCreation($key, $object)
    {
        $this->applyServices(get_class($object), $object);
        $this->applyServices($key, $object);
    }

    public function applyServices($key, $object)
    {
        if (!isset($this->services[$key])) {
            return;
        }

        if (is_string($this->services[$key])) {
            $class = $this->services[$key];
        }

        foreach ($this->services[$key] as $factory => $arr) {
            foreach ($object->{$factory . 'Factory'}->create($arr) as $service) {
                $object->{'add' . ucfirst($factory)}($service);
            }
        }
    }

    public function getMapper()
    {
        return $this->mapper;
    }

    public function getMapperKeys()
    {
        return array_keys($this->mapper);
    }
}
