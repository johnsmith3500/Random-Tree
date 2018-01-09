<?php
	// db_rand_tree_gen.php - generating tree in a mysql table

	define ('MAX_RECS_ON_LEVEL', 3);	// max number records on a level (from one parent)
	define ('MAX_DEPTH', 5);	// max depth (max number of levels)

	// mysql credentials
	define ('USERNAME', 'username'); 	// mysql username
	define ('PASSWORD', 'password');	// mysql password
	define ('DB_NAME', 'database_name');	// mysql database name
	define ('TABLE', 'table_name');		// mysql table name
	define ('HOST', 'localhost');	// mysql host

	// Function for generating random words: 2-3 uppercase letters
	function get_random_string()
	{
		$str='';
		for($i=0; $i<rand(2, 3); $i++)
			$str.=chr(rand(65, 90));
		return $str;
	}

	// Function for inserting records in the db
	// $level - depth of the records(0 - all records will be in the root),
	// 1 - root records may have children etc
	function insert_records($parent_id=0, $level=0)
	{
		global $dbc;
		srand();	// seeding random generator 
		$number_recs_on_the_level=rand(0, MAX_RECS_ON_LEVEL);	// number of records on the level (from the parent)

		
		// For each record: write it into db, recursively call insert_records() if we have not reach the bottom yet
		for($i=0; $i<$number_recs_on_the_level; $i++)	
		{
			$str=get_random_string();
			$q="insert into ".TABLE." (name, parent_id) values ('$str', $parent_id)";
			$r=mysqli_query($dbc, $q);
			$id=mysqli_insert_id($dbc);
			if($level>0)	// Do we need children?
			{
				insert_records($id, $level-1);
			}
		}
	}

	// connect to db
	$dbc=mysqli_connect(HOST, USERNAME, PASSWORD, DB_NAME);

	// if table already exists - drop it
	$q="drop table if exists ".TABLE.";";
	$r=mysqli_query($dbc, $q);
	if($r === false)
	{
		print "Error! Cannot drop table.\n";
		print mysqli_error($dbc)."\n";
		exit(1);
	}

	// create table 
	$q="create table ".TABLE." (
		id int(11) not null auto_increment primary key,
		name tinytext not null,
		parent_id int(11)
		)";
	$r=mysqli_query($dbc, $q);
	if($r === false)
	{
		print "Error! Cannot create table.\n";
		print mysqli_error($dbc)."\n";
		exit(1);
	}
	
	// Generate tree in mysql
	insert_records(0, MAX_DEPTH);	
