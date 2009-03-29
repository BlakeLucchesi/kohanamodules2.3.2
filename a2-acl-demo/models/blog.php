<?php

class Blog_Model extends ORM implements Acl_Resource_Interface {

	public function get_resource_id()
	{
		return 'blog';
	}

	public function validate(array & $array, $save = FALSE)
	{
		$array = Validation::factory($array)
			->pre_filter('trim')
			->add_rules('text','required');

		return parent::validate($array, $save);
	}

} // End Blog Model