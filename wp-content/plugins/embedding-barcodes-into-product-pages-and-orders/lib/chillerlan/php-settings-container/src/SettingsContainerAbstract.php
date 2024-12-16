<?php

namespace chillerlan\Settings;

use ReflectionClass, ReflectionProperty;

use function call_user_func, call_user_func_array, get_object_vars, json_decode, json_encode, method_exists, property_exists;
use const JSON_THROW_ON_ERROR;

abstract class SettingsContainerAbstract implements SettingsContainerInterface{

	public function __construct(iterable $properties = null){

		if(!empty($properties)){
			$this->fromIterable($properties);
		}

		$this->construct();
	}

	protected function construct():void{
		$traits = (new ReflectionClass($this))->getTraits();

		foreach($traits as $trait){
			$method = $trait->getShortName();

			if(method_exists($this, $method)){
				call_user_func([$this, $method]);
			}
		}

	}

	public function __get(string $property){

		if(!property_exists($this, $property) || $this->isPrivate($property)){
			return null;
		}

		$method = 'get_'.$property;

		if(method_exists($this, $method)){
			return call_user_func([$this, $method]);
		}

		return $this->{$property};
	}

	public function __set(string $property, $value):void{

		if(!property_exists($this, $property) || $this->isPrivate($property)){
			return;
		}

		$method = 'set_'.$property;

		if(method_exists($this, $method)){
			call_user_func_array([$this, $method], [$value]);

			return;
		}

		$this->{$property} = $value;
	}

	public function __isset(string $property):bool{
		return isset($this->{$property}) && !$this->isPrivate($property);
	}

	protected function isPrivate(string $property):bool{
		return (new ReflectionProperty($this, $property))->isPrivate();
	}

	public function __unset(string $property):void{

		if($this->__isset($property)){
			unset($this->{$property});
		}

	}

	public function __toString():string{
		return $this->toJSON();
	}

	public function toArray():array{
		return get_object_vars($this);
	}

	public function fromIterable(iterable $properties):SettingsContainerInterface{

		foreach($properties as $key => $value){
			$this->__set($key, $value);
		}

		return $this;
	}

	public function toJSON(int $jsonOptions = null):string{
		return json_encode($this, $jsonOptions ?? 0);
	}

	public function fromJSON(string $json):SettingsContainerInterface{
		$data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

		return $this->fromIterable($data);
	}

	public function jsonSerialize():array{
		return $this->toArray();
	}

}
