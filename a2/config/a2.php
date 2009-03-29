<?php

/*
 * The Authentication library to use
 * Make sure that the library supports a get_user method that returns FALSE when no user is logged in
 * and a user object that implements Acl_Role_Interface when a user is logged in.
 */
$config['a1'] = A1::instance('a1');

/*
 * The ACL Roles (String IDs are fine, use of ACL_Role_Interface objects also possible)
 * Use: ROLE => PARENT(S) (make sure parent is defined as role itself before you use it as a parent)
 */
$config['roles'] = array
(
	'user'			=>	'guest',
	'admin'			=>	'user'
);

/*
 * The name of the guest role 
 * Used when no user is logged in.
 */
$config['guest_role'] = 'guest';

/*
 * The ACL Resources (String IDs are fine, use of ACL_Resource_Interface objects also possible)
 * Use: ROLE => PARENT (make sure parent is defined as resource itself before you use it as a parent)
 */
$config['resources'] = array
(
	'blog'				=>	NULL
);

/*
 * The ACL Rules (Again, string IDs are fine, use of ACL_Role/Resource_Interface objects also possible)
 * Split in allow rules and deny rules, one sub-array per rule:
     array( ROLES, RESOURCES, PRIVILEGES, ASSERTION)
 */
$config['rules'] = array
(
	'allow' => array
	(
			// guest can read blog
		array('guest','blog','read'),
		
			// users can add blogs
		array('user','blog','add'),
		
			// users can edit their own blogs (and only their own blogs)
		array('user','blog','edit',new Acl_Assert_Argument(array('primary_key_value'=>'user_id'))),
		
			// administrators can delete everything 
		array('admin','blog','delete'),
	),
	'deny' => array
	(
		  // no deny rules in this example
	)
);