<?php
 abstract class Model
 {
	 private $the_query;
	 private $map = array();
	 private $link_to;

	 private $variables;
	 private $publics_variables;
	 private $privates_variables;

	 private $dont_parse = array('class_name', 'map', 'comment', 'link_to', 'variables', 'publics_variables', 'privates_variables', 'dont_parse');

	 function __construct() {;}

	 public static function _new(){
	 	$obj = self::new_object();
		foreach( get_class_vars( get_class( $obj ) ) as $key => $value ) {
			 $obj->$key = $value;
		}
		
		return $obj;
	 }

	 function _save() {
	 	if( isset($this->variables['id']) ) {
		 	$this->update_object();
	 	}
	 	else {
		 	$this->add_object();
	 	}

	 	return $this;
	 }

	 function _all() {
		 $objects = $this->all_objects();

		 return $objects;
	 }

	 function _find() {
	 	 $ids = func_get_args();

	 	 if( sizeof($ids) > 1 ) {
		 	 return $this->specific_objects( $ids );
	 	 }
	 	 else {
		 	 $id = $ids[0];
		 	 return $this->single_object( $id );
	 	 }

	 }
	 function _where( $array = array() ) {
		$CLASS_NAME = $this->link_to;

	 	if(!empty($array)) {
	 		$where = 'WHERE ';
	 		foreach( $array as $key=>$value) {
	 			$where[] =$key.' = "'.$value;
	 		}

	 		$where = implode(' AND ', $where);

	 		$query = 'SELECT '.$CLASS_NAME.'.* FROM '.$CLASS_NAME.' '.$where;
	 		$this->the_query = $query;
			return $this->multiple_objects( $query );
	 	}
	 	else {
	 		throw new Exception("You need to enter parameter", 1);
	 	}
	 }

	 function _query( $query = null ) {
	 	if( !is_null($query) ){
	 		return $this->multiple_objects($query);
	 	}
	 }

	 function _sql( $array = array() ) {
	 	if(!empty($array)) {
	 		$where = '';
	 		$order = '';
	 		$limit = '';

	 		if(isset($array['where'])) {
		 		$where = 'WHERE ';
		 		foreach( $array['where'] as $value) {
		 			$where[] = $value['key'].' '.$value['compare'].' "'.$value['value'];
		 		}

		 		$where = implode(' AND ', $where);
		 	}
		 	if( isset($array['order']) ){
		 		$orderby = (isset($array['order']['by'])?$array['order']['by']:'DESC');
		 		$order = 'ORDER BY '.$array['order']['key'].' '.$orderby;
		 	}
		 	if( isset($array['limit']) ){
		 		$limit = 'LIMIT '.$array['limit']['x'];
		 		if( $array['limit']['y'] ){
		 			$limit .= ', '.$array['limit']['y'];
		 		}
		 	}
		 	$CLASS_NAME = $this->link_to;

	 		$query = 'SELECT '.$CLASS_NAME.'.* FROM '.$CLASS_NAME.' '.$where.' '.$order.' '.$limit;
	 		$thi->the_query = $query;
			return $this->multiple_objects( $query );
	 	}
	 	else {
	 		throw new Exception("You need to enter parameter", 1);
	 	}
	 }

	 function _limit( $nombre = 10, $paged = 1 ) {
	 	$CLASS_NAME = $this->link_to;
 		$start = $nombre * ($paged - 1);
 		$query = 'SELECT '.$CLASS_NAME.'.* FROM '.$CLASS_NAME.' LIMIT '.$start.','.$nombre;
 		$this->the_query = $query;
 		return $this->multiple_objects( $query );
	 }

	

	 function _order( $order_by = array() ) {
	 	$CLASS_NAME = $this->link_to;
	 	if( !empty($order_by) ) {
	 		foreach( $order_by as $key=>$value) {
	 			$order = ( !is_int($key) ? $value : 'ASC' );
	 			$order_by = ( !is_int($key) ? $key : $value );
	 			$by[] =$order_by.' '.$order;
	 		}
	 		$sort = implode(',', $by);
	 		$query = 'SELECT '.$CLASS_NAME.'.* FROM '.$CLASS_NAME.' ORDER BY '.$sort;
	 		$this->the_query = $query;
	 		return $this->multiple_objects( $query );
	 	}
	 	else{
	 		throw new Exception("You need to enter a value to sort by", 1);
	 		
	 	}
	 }	

	 function _create() {
		 $values = func_get_args();
		 if( isset($values) ){
		 	 $sets = '';
			 foreach( $values[0] as $key=>$value ) {
			 	$this->variables[$key] = $value;
			 }
			 if($this->add_object())
			 	return $this;

			 return false;
		 }
	 }

	 function _values() {
		 $values = func_get_args();
		 if( isset($values) ){
			 foreach( $values[0] as $key=>$value ) {
			 	$this->variables[$key] = $value;
			 }
		 }
		 return $this;
	 }

	 function _count() {
	 	 $CLASS_NAME = $this->link_to;
		 $query = 'SELECT COUNT(id) AS count FROM '.$CLASS_NAME;
		 $STH = Connection::prepare()->query($query);
		 $count = $STH->fetch();

		 return $count['count'];
	 }

	 function _update() {
		 $values = func_get_args();
		 if( isset($values) ){
		 	 $sets = '';
			 foreach( $values[0] as $key=>$value ) {
			 	$this->$key = $value;
			 }
			 $this->update_object();
		 }

		 return $this;
	 }

	 function _delete() {
		 if( isset($this->variables['id']) ) {
			 $query = 'DELETE FROM '.$this->link_to.' WHERE id='.$this->variables['id'];
			 try {
			 	 $STH = Connection::prepare()->query($query);
				 if( $STH ) {
				 	 unset( $this );
				 	 foreach ($this->variables as $key => $value) {
				 	 	$this->variables[$key] = '';
				 	 }
					 return true;
				 }
				 else {
					 return false;
				 }
			 } catch (PDOException $e) {
		  		echo $e->getMessage();
		  	}
		 }
		 else {
			 throw new Exception('You need to delete a specific '.$this->link_to);
		 }
	 }

	 function link( $link ){
	 	$this->link_to = $link;
	 	// Find table variables
	 	$q = Connection::prepare()->prepare("DESCRIBE $link");
		$q->execute();
		$variables = $q->fetchAll(PDO::FETCH_COLUMN);

		foreach ($variables as $key => $value) {
			$this->variables[$value] = '';
		}
	 }

	 function publics_variables( $publics_variables ){
	 	$this->publics_variables = $publics_variables;
	 }

	 function privates_variables( $privates_variables ){
	 	$this->privates_variables = $privates_variables;
	 }

	 function _map( $object = null ) {
	 	$CLASS_NAME = $this->link_to;
	 	$id = $this->variables['id'];
	 	if( isset($object) )  {
	 		$search = $object::new_object()->link_to;
	 		if( $this->search_for_link($search, "belongs_to") ) {
	 			$link = $this->search_for_link($search, "belongs_to");
	 			$query = "SELECT $search.* FROM $search JOIN $CLASS_NAME ON $CLASS_NAME.$link = $search.id WHERE $CLASS_NAME.id = $id";
	 			$new_object = $object::new_object()->single_object_query($query);
	 			
	 			return $new_object;
	 		}
	 		elseif( $this->search_for_link($search, 'has_many') ) {
	 			$link = $this->search_for_link($search, 'has_many');
	 			$query = "SELECT $search.* FROM $search JOIN $CLASS_NAME ON $search.$link = $CLASS_NAME.id WHERE $CLASS_NAME.id = $id";
	 			$new_objects = $object::new_object()->multiple_objects($query);

	 			return $new_objects;
	 		}
	 	}
	 }

	 private function search_for_link($object, $link) {
	 	$find = false;
	 	if(isset($this->map[$link]) && !empty($this->map[$link])) {
	 		foreach($this->map[$link] as $specific) {
		 		if(array_search($object, $specific)) {
		 			$find = $specific['link'];	
		 		} 
		 	}
	 	}
	 	
	 	return $find;
	 }

	 function have_one( $object = null, $link = null ) {
	 	if( !is_null($object) && !is_null($link) ) {
	 		$this->map['have_one'][] = array( 'object' => $object, 'link' => $link );
	 	}
	 }

	 function belongs_to( $object = null, $link = null ) {
	 	if( !is_null($object) && !is_null($link) ) {
	 		$this->map['belongs_to'][] = array( 'object' => $object, 'link' => $link );
	 	}
	 }

	 function has_many( $object = null, $link = null ) {
	 	if( !is_null($object) && !is_null($link) ) {
	 		$this->map['has_many'][] = array( 'object' => $object, 'link' => $link );
	 	}
	 }

	 function _first() {
	 	$CLASS_NAME = $this->link_to;
		return $this->single_object_query( 'SELECT '.$CLASS_NAME.'.* FROM '.$CLASS_NAME.' ORDER BY id ASC LIMIT 1' );
	 }	

	 function _last() {
	 	$CLASS_NAME = $this->link_to;
		return $this->single_object_query( 'SELECT '.$CLASS_NAME.'.* FROM '.$CLASS_NAME.' ORDER BY id DESC LIMIT 1' );
	 }

	 function __get( $key ) {
		 	if( isset($this->variables[$key]) ) {
				return ( $this->accessible_variable($key) ? $this->variables[$key] : '' );
			}
			else {
				throw new Exception('WOOT nothing found');
			}
	 }
	 function __set( $key, $value ) {
		 if( isset( $key ) && isset( $value )) {
			 $this->variables[$key] = $value;
		 }
		 else {
			 throw new Exception('WOOT You need to modify a specific value');
		}
	 }

	 private function accessible_variable( $key ){
	 	if( isset($this->variables[$key]) ){
	 		if( (count($this->publics_variables) > 0 && in_array($key, $this->publics_variables)) || count($this->publics_variables) === 0 ){
	 			if( count($this->privates_variables) === 0 || (count($this->privates_variables) > 0 && !in_array($key, $this->privates_variables)) ){
	 				return true;
	 			}
	 		}
	 	}

	 	return false;
	 }

	 private function single_object( $id = null ){
		  if( isset( $id ) ) {
		  	$table_name = $this->link_to;
		  	  
		  	try {
		  		$STH = Connection::prepare()->query('SELECT '.$table_name.'.* FROM '.$table_name.' WHERE id='.$id);
		  		$STH->setFetchMode(PDO::FETCH_ASSOC);	

		  		foreach( $STH->fetch() as $key=>$value ) {
						$this->variables[$key] = $value;
					}

					return $this;
		  	} catch (PDOException $e) {
		  		echo $e->getMessage();
		  	}
		 }
	 }

	 private function single_object_query( $query = null ) {
	 	if( isset( $query ) ) {
	 		$table_name = $this->link_to;
	 		
	 		try {
		  		$STH = Connection::prepare()->query($query);
		  		$STH->setFetchMode(PDO::FETCH_ASSOC);	

		  		foreach( $STH->fetch() as $key=>$value ) {
						$this->variables[$key] = $value;
					}

					return $this;
	  	} catch (PDOException $e) {
	  		echo $e->getMessage();
	  	}
	 	}
	 } 

	 private function specific_objects( $ids = null ){
		  if( isset( $ids ) ){
			 $objects = array();
			 $value = '';
			 foreach( $ids as $id ){
				 $value .= 'id='.$id.' OR ';
			 }
			 $table_name = $this->link_to;
			 $value = substr( $value, 0, strlen($value)-3 );

			 $query = 'SELECT '.$table_name.'.* FROM '.$table_name.' WHERE '.$value;
			 
			 return $this->multiple_objects( $query );
		 }
		 else {
			 throw new Exception('Enter valid ID');
		 }
	 }

	 private function all_objects(){
		$query = 'SELECT '.$this->link_to.'.* FROM '.$this->link_to;

		return $this->multiple_objects( $query );
	 }

	 function multiple_objects( $query ){
	 	 $CLASS_NAME = $this->link_to;
		 if( $query ){
		 	$objects = array();
		 	$obj_vide = self::new_object();


	 		try {
	  		$STH = Connection::prepare()->query($query);
	  		$STH->setFetchMode(PDO::FETCH_ASSOC);	

	  		while( $object = $STH->fetch() ){
	  			$obj = self::new_object();
					 foreach( $object as $key => $value ){
						 $obj->variables[$key] = $value;
					 }
					 $objects[$obj->variables['id']] = $obj;
	  		}

	  		$objects_array = new Model_array($objects);
			 	$objects_array->query($query);
			 	$objects_array->obj($obj_vide);

				return $objects_array;

	  	} catch (PDOException $e) {
	  		echo $e->getMessage();
	  	}
		 }
		 else {
			 throw new Exception('An eror happen when you try to find many '.$CLASS_NAME);
		 }

	 }

	 private function add_object() {
		$keys   = '';
	 	$values = '';
	 	$table_name = $this->link_to;
		foreach( $this->variables as $key=>$value ) {
		 	if( isset( $this->variables[$key] ) && !in_array($key, $this->dont_parse)) {
			 	$keys[] = $key;
			 	$values[] = '\''.$this->$key.'\'';
		 	}
	 	}

	 	$keys = implode(',', $keys);
	 	$values = implode(',', $values);
	 	$query = 'INSERT INTO '.$table_name.' ('.$keys.') VALUES('.$values.')';

	 	$pdo_object = Connection::prepare();
	 	$STH = $pdo_object->query($query);
	 	if( $STH ) {
		 	$this->variables['id'] = $pdo_object->lastInsertId();
		 	return true;
	 	}
	 	else
	 	{
		 	return false;
	 	}
	 }

	 private function update_object() {
		$sets = '';
		foreach( $this->variables as $key=>$value ) {
		 	if( isset( $this->variables[$key] ) && !in_array($key, $this->dont_parse) ) {
			 	$sets[] = $key.'= \''. $this->$key.'\'';
		 	}
	 	}
	 	$sets = implode(',', $sets);
	 	$query = 'UPDATE '.$this->link_to.' SET '.$sets.'WHERE id='.$this->variables['id'];
	 	$STH = Connection::prepare()->query($query);

	 	if( $STH ) {
		 	return true;
	 	}
	 	else {
		 	return false;
	 	}
	 }

	 private static function new_object() {
		 return new static();
	 }
 }

 class Model_array extends ArrayObject 
 {	
 	private $query;
 	private $obj;
 	function __construct( $array_data ) {
 		parent::__construct($array_data,ArrayObject::ARRAY_AS_PROPS);
 	}

 	function query($query){
 		$this->query = $query;
 	}

 	function obj($obj) {
 		$this->obj = $obj;
 	}

 	function _find( $id = null )
 	{
 		if( isset($id) ) {
 			$data = $this->getArrayCopy();

 			return $data[$id];
 		}
 	}

 	function _first() {
 		$data = $this->getArrayCopy();
 		$first_element = array_shift($data);

 		return $first_element;
 	}

 	function _last() {
 		$data = $this->getArrayCopy();
 		$last_element = end($data);

 		return $last_element;
 	}

 	function _order($order_by = array()) {
 		$args = func_get_args();
 		$by = '';
 		foreach( $order_by as $key=>$value) {
 			$order = ( !is_int($key) ? $value : 'ASC' );
 			$order_by = ( !is_int($key) ? $key : $value );
 			$by[] =$order_by.' '.$order;
 		}
 		$sort = implode(',', $by);
 		$query = $this->query.' ORDER BY '.$sort;
 
 		return $this->obj->multiple_objects( $query );
 	}

 	 function _limit( $nombre = 10, $paged = 1 ) {
 		$start = $nombre * ($paged - 1);
 		$query = $this->query.' LIMIT '.$start.','.$nombre;
 		$obj->the_query = $query;

 		return $this->obj->multiple_objects( $query );
	 }

	 function _count(){
	 	return count($this->getArrayCopy());
	 }
 }
 class Connection
	{
		static function prepare()
		{
			$host   = "localhost";
			$dbname = "orm";
			$user   = "root";
			$pass   = "root";

			try {  
			  $DBH = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);  
			  
			  $DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION ); 
				
				return $DBH;
			}  
			catch(PDOException $e) {  
		    echo $e->getMessage();  
			}
		}
	} 
 ?>