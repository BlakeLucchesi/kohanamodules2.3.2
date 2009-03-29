<?php

class User_Model extends Model_A1_User implements Acl_Role_Interface {

	public function get_role_id()
	{
		return $this->role;
	}

	/**
	 * Validates and optionally saves a new user record from an array.
	 *
	 * @param  array    values to check
	 * @param  boolean  save the record when validation succeeds
	 * @return boolean
	 */
	public function validate(array & $array, $save = FALSE)
	{
		$array = Validation::factory($array)
			->pre_filter('trim')
			->add_rules('username', 'required', 'length[4,32]', 'chars[a-zA-Z0-9_.]', array($this, 'username_available'))
			->add_rules('password', 'length[5,42]')
			->add_rules('password_confirm', 'equals[password]')
			->add_rules('role','matches[user,admin]');

		if ( ! $this->loaded)
		{
			// This user is new, the password must be provided
			$array->add_rules('password', 'required');
		}

		return parent::validate($array, $save);
	}

} // End User Model