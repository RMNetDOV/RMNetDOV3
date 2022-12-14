<?php

class node {
	var $childs;
	var $data;
	var $id;
	var $parent;
}

class tree
{

	var $obj;
	var $events;

	// Feld Definitionen
	var $data_field  = 'name';
	var $primary_field = 'media_cat_id';
	var $parent_field  = 'parent';
	var $root_id   = '0';
	var $opt_spacer  = '&nbsp;';

	// interne Vars
	var $_last_id;

	/*
		Funktion zum laden des Baumes aus Array
	*/

	function loadFromArray ($nodes) {

		$this->obj[$this->root_id] = new node();
		if(is_array($nodes)) {
			foreach($nodes as $row) {

				$id = $row[$this->primary_field];
				$data = $row[$this->data_field];
				$ordner = $row[$this->parent_field];

				//$this->raw_data[$id] = $row;

				if($id > $this->_last_id) $this->_last_id = $id;

				if(!is_object($this->obj[$id])) $this->obj[$id] = new node();

				$this->obj[$id]->data = $data;
				$this->obj[$id]->id = $row[$this->primary_field];
				$this->obj[$id]->parent = $row[$this->parent_field];

				if(is_object($this->obj[$ordner])) {
					$this->obj[$ordner]->childs[$id] = &$this->obj[$id];
				} else {
					$this->obj[$ordner] = new node();
					$this->obj[$ordner]->childs[$id] = &$this->obj[$id];
				}
			}
		}
	}

	function optionlist($nroot = '')
	{

		if($nroot == '') $nroot = $this->obj[$this->root_id];
		$opt_spacer = $this->opt_spacer;

		$this->ptree($nroot, '', $optionlist, $opt_spacer);

		if(is_array($optionlist)){
			return $optionlist;
		} else {
			return false;
		}
	}

	function ptree($myobj, $ebene, &$optionlist, $opt_spacer){
		$ebene .= $opt_spacer;

		if(is_array($myobj->childs)) {
			foreach($myobj->childs as $val) {
				$id = $val->id;
				if(!empty($id)) $optionlist[$id] = array(  data => $ebene . $val->data,
						id => $id);
				$this->ptree($val, $ebene, $optionlist, $opt_spacer);
			}
		}
	}

	function add($parent, $data) {

		$id = $this->_last_id + 1;
		$this->obj[$id] = new node;
		$this->obj[$id]->data = $data;
		$this->obj[$id]->id = $id;
		$this->obj[$id]->parent = $parent;
		$this->obj[$parent]->childs[$id] = &$this->obj[$id];

		// Event Aufrufen
		$this->_callEvent('insert', $this->obj[$id]);

	}

	/*
    	L??schen von Eintr??gen ohne Child's
    */

	function del($id) {
		if(count($this->obj[$id]->childs) == 0) {
			$this->obj[$id] = NULL;
			unset($this->obj[$id]);
			return true;
		} else {
			return false;
		}
	}

	/*
    	Rekursives l??schen von Eintr??gen
    */

	function deltree($tree_id) {
		// l??sche Eintr??ge recursiv
		$this->_deltree_recurse($this->obj[$this->root_id], $tree_id, 0);
	}

	/*
    	Hilfsfunktion f??r deltree
    */

	function _deltree_recurse(&$myobj, $tree_id, $delete) {
		if(is_array($myobj->childs)) {
			foreach($myobj->childs as $val) {

				// Setze Delete Flag
				if($val->id == $tree_id) {
					$delete = 1;
				}

				// recurse durch Objekte
				$this->_deltree_recurse($val, $tree_id, $delete);

				// l??sche Eintrag
				if($delete == 1) {
					$tmp_id = $val->id;
					$this->obj[$tmp_id] = NULL;
					unset($this->obj[$tmp_id]);
					$this->_callEvent('delete', $val);
					//echo "Deleting ID: $tmp_id \r\n";
				}

				// entferne Delete Flag
				if($val->id == $tree_id) {
					$delete = 0;
				}
			}
		}
	}


	/*
    	private Funktion zum aufrufen der eventHandler
    */

	function _callEvent($event, $myobj, $myobj_old = '') {
		global $app;
		if(is_array($this->events)) {
			foreach($this->events as $val) {
				if($val["event"] == $event) {
					$class_name = $val["class_name"];
					$function_name = $val["function_name"];
					if($val["class_name"] != '') {
						$app->uses($class_name);
						$app->$class_name->$function_name($myobj, $myobj_old);
					} else {
						call_user_func($function_name, $myobj, $myobj_old);
					}
				}
			}
		}
	}

	/*
    	Funktion zum Verschieben von Eintr??gen
    */

	function move($id, $new_parent) {

		$obj_old = $this->obj[$id];
		$parent = $this->obj[$id]->parent;
		$this->obj[$new_parent]->childs[$id] = &$this->obj[$id];
		$this->obj[$id]->parent = $new_parent;
		unset($this->obj[$parent]->childs[$id]);

		// event aufrufen
		$this->_callEvent('update', $this->obj[$id], $obj_old);

	}

	/*
    	Funktion zum updaten der Daten eines Nodes
    */

	function update($id, $data) {

		$obj_old = $this->obj[$id];
		$this->obj[$id]->data = $data;
		$this->_callEvent('update', $this->obj[$id], $obj_old);

	}

	/*
    	Funktion zum registrieren von Events
    	m??gliche events: insert, update, delete

    */

	function regEvent($event, $class_name, $function_name) {

		$this->events[] = array(  event    => $event,
			class_name   => $class_name,
			function_name  => $function_name);

	}

}


?>
