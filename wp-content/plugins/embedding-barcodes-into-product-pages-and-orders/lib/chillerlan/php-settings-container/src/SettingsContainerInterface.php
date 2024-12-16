<?php

namespace chillerlan\Settings;

use JsonSerializable;

interface SettingsContainerInterface extends JsonSerializable{

	public function __get(string $property);

	public function __set(string $property, $value):void;

	public function __isset(string $property):bool;

	public function __unset(string $property):void;

	public function __toString():string;

	public function toArray():array;

	public function fromIterable(iterable $properties):SettingsContainerInterface;

	public function toJSON(int $jsonOptions = null):string;

	public function fromJSON(string $json):SettingsContainerInterface;

}
