<?php

/* 
 * Abstract A1 Authentication User Model
 * To be extended and completed to user's needs
 */

abstract class A1_User_Model extends ORM {

	// Specify config name so password gets hashed correctly (with the right salt pattern) when set in user
	protected $config_name = 'a1';

	// Columns to ignore
	protected $ignored_columns = array('password_confirm');

	public function __set($key, $value)
	{
		if ($key === 'password')
		{
			if ($this->loaded AND $value === '')
			{
				// Do not set empty passwords
				return;
			}

			// Use Auth to hash the password
			$value = A1::instance($this->config_name)->hash_password($value);
		}

		parent::__set($key, $value);
	}

	/**
	 * Validates an array for a matching password and password_confirm field.
	 *
	 * @param  array    values to check
	 * @param  string   save the user if
	 * @return boolean
	 */
	public function change_password(array & $array, $save = FALSE)
	{
		$array = Validation::factory($array)
			->pre_filter('trim')
			->add_rules('password', 'required', 'length[5,127]')
			->add_rules('password_confirm', 'matches[password]');

		if ($status = $array->validate())
		{
			// Change the password
			$this->password = $array['password'];

			if ($save !== FALSE AND $status = $this->save())
			{
				if (is_string($save))
				{
					// Redirect to the success page
					url::redirect($save);
				}
			}
		}

		return $status;
	}

	/**
	 * Tests if a username exists in the database. This can be used as a
	 * Valdidation rule.
	 *
	 * @param   mixed    id to check
	 * @return  boolean
	 */
	public function username_available($id)
	{
		$key = $this->unique_key($id);

		if ($this->loaded AND $this->$key === $id)
		{
			// This value is unchanged
			return TRUE;
		}

		return ! ORM::factory('user')->where($key, $id)->count_all();
	}

	/**
	 * Allows a model to be loaded by username.
	 */
	public function unique_key($id)
	{
		if ( ! empty($id) AND is_string($id) AND ! ctype_digit($id))
		{
			return 'username';
		}

		return parent::unique_key($id);
	}

} // End Auth User Model