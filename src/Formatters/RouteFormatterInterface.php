<?php

namespace Raml\Formatters;

interface RouteFormatterInterface
{
	public function format(array $resources);
}