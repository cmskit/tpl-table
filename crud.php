<?php

// id-translation for the main list
if (!isset($_GET['objectId'])) { $_GET['objectId'] = $_GET['id']; }

class table_crud extends crud
{
	private function setSortBy()
	{
		// fix sorting
		if (!empty($_GET["jtSorting"]))
		{
			if (substr($_GET["jtSorting"], -4) == 'DESC')
			{
				$this->sortBy = array(trim(substr($_GET["jtSorting"], 0, -4)) => 'desc' );
			}
			else
			{
				$this->sortBy = array(trim(substr($_GET["jtSorting"], 0, -3)) => 'asc' );
			}
		}
	}
	
	// Getting records (listAction)
	public function getList()
	{
		$o = $this->projectName . '\\' . $this->objectName;
		$obj = new $o();
		$this->setSortBy();
		
		$jTableResult = array();
		
		/*
		// test-output
		$jTableResult['Result'] = "ERROR";
		$jTableResult['Message'] = json_encode($_GET);
		return json_encode($jTableResult);
		*/
		
		$db = $this->projectName . '\\DB';
		$jTableResult['TotalRecordCount'] = $db::instance($obj::DB)->query('SELECT COUNT(*) AS c FROM `'.$this->objectName.'`')->fetch()->c;
		
		
		if (isset($_POST['q']))
		{
			for($i=0; $i<count($_POST['q']); $i++)
			{
				if(!empty($_POST['q'][$i])) $this->getListFilter[] = array($_POST['opt'][$i], 'LIKE', '%'.$_POST['q'][$i].'%');
			}
		}
		//file_put_contents('test.txt',json_encode($this->getListFilter));
		
		
		$records = $obj->GetList($this->getListFilter, $this->sortBy, $_GET['jtPageSize'], $_GET['jtStartIndex']);
		
		// loop the records and check for crappy/long content in text-fields
		foreach ($records as $i => $r)
		{
			foreach ($r as $k => $v)
			{
				if (!empty($v) && $this->objects[$this->objectName]['col'][$k]['type'] == 'TEXT')
				{
					$v = strip_tags($v);
					$s = substr($v, 0, 100);
					$p = strrpos($s, ' ');
					if ($p > 0) $s = substr(strip_tags($v), 0, $p);
					if(strlen($v) > strlen($s)) $s .= '...';
					$records[$i]->{$k} = $s;
					//file_put_contents('test.txt', $records[$i]->{$k});
				}
			}
		}
		//file_put_contents('test.txt',json_encode($records));
		
		$jTableResult['Records'] = $records;
		$jTableResult['Result'] = 'OK';
		if (!is_array($jTableResult['Records']))
		{
			$jTableResult['Result'] = 'ERROR';
			$jTableResult['Message'] = json_encode($jTableResult['Records']);
		}
		return json_encode($jTableResult);
	}
	
	// Creating a new record (createAction)
	public function createNewContent()
	{
		$id = $this->createContent();
		$jTableResult = array();
		
		/*
		$jTableResult['Result'] = 'ERROR';
		$jTableResult['Message'] = $id;
		return json_encode($jTableResult);
		*/
		
		if (is_numeric($id))
		{
			$this->objectId = $id;
			$x = $this->saveContent();
			$jTableResult['Result'] = 'OK';
			$o = $this->projectName . '\\' . $this->objectName;
			$obj = new $o();
			$jTableResult['Record'] = $obj->Get($id);
		}
		else
		{
			$jTableResult['Result'] = 'ERROR';
			$jTableResult['Message'] = $id;
		}
		return json_encode($jTableResult);
	}
	
	// Updating a record (updateAction)
	public function updateContent()
	{
		$this->objectId = $_POST['id'];
		$jTableResult = array();
		$msg = $this->saveContent();
		
		// we have to deal with a Connection
		if (isset($_GET['referenceName']))
		{
			require_once $this->ppath.'/objects/class.'.$this->referenceName.'.php';
			
			switch ($_GET['referenceType'])
			{
				case 's':
					$on = $this->projectName . '\\' . $this->objectName;
					$o = new $on();
					$oe = $o->Get($this->objectId);
					$or = $this->projectName . '\\' . $this->referenceName;
					$r = new $or();
					$re = $r->Get($this->referenceId);
					$n = array($this->objectName, $this->referenceName);
					natsort($n);
					$map = $this->projectName . '\\' . implode('',$n).'map';
					$m = new $map();
					$what = array('RemoveMapping','AddMapping');
					$m->{$what[intval($_POST['__connected__'])]}($oe, $re);
				break;
				case 'c':
					// todo
					
				break;
				case 'p':
					// todo
					
				break;
			}
		}// Connection END
		
		
		if(substr($msg,0,2) == '[[')
		{
			$jTableResult['Result'] = 'ERROR';
			$jTableResult['Message'] = $msg;
		}
		else
		{
			$jTableResult['Result'] = 'OK';
		}
		return json_encode($jTableResult);
	}
	
	// Deleting a record (deleteAction)
	public function removeContent()
	{
		$this->objectId = $_POST['id'];
		$jTableResult = array();
		$msg = $this->deleteContent();
		if(substr($msg,0,2) == '[[')
		{
			$jTableResult['Result'] = 'ERROR';
			$jTableResult['Message'] = $msg;
		}
		else
		{
			$jTableResult['Result'] = 'OK';
		}
		return json_encode($jTableResult);
	}
	
	//////////////////////////////////// show SUB-ENTRIES ///////////////////////////////////////////
	
	public function getConnectedReferences()
	{
		$jTableResult = array();
		
		// main object
		$o = $this->projectName . '\\' . $this->objectName;
		$obj = new $o();
		$item = $obj->Get($this->objectId);
		
		// reference-object
		require_once($this->ppath.'/objects/class.'.$this->referenceName.'.php');
		$or = $this->projectName . '\\' . $this->referenceName;
		$ref = new $or();
		
		if (isset($_POST['q']))
		{
			for($i=0; $i<count($_POST['q']); $i++)
			{
				$this->getAssocListFilter[] = array($_POST['opt'][$i], 'LIKE', '%'.$_POST['q'][$i].'%');
			}
		}
		
		$this->setSortBy();
		
		// check for reference-type
		switch ($_GET['referenceType'])
		{
			case 's':
				$call = 'Get'.$this->referenceName.'List';
			break;
			case 'p':
				$call = 'Get'.$this->referenceName.'List';
			break;
			case 'c':
				$call = 'Get'.$this->referenceName.'List';
			break;
		}
		
		$records = $item->$call($this->getAssocListFilter, $this->sortBy);
		
		
		if (!is_array($records))
		{
			$jTableResult['Result'] = 'ERROR';
			$jTableResult['Message'] = json_encode($records);
		}
		else
		{
			
			$jTableResult['Result'] = 'OK';
			$jTableResult['Records'] = array();
			$cc = 0;
			$conns = array();
			
			
			// 1. collect the connected references
			foreach ($records as $r)
			{
				if ($cc >= intval($_GET['jtStartIndex']) && $cc < ($_GET['jtPageSize']*($_GET['jtStartIndex']+1)))
				{
					$conns[] = $r->id;
					$jTableResult['Records'][] = array_merge(array('__connected__'=>'1'), $this->objectToArray($r));
				}
				$cc++;
			}
			
			
			// 2. now collect the rest
			$refList = $ref->GetList($this->getAssocListFilter, $this->sortBy);
			foreach ($refList as $r)
			{
				if (!in_array($r->id, $conns))
				{
					if($cc >= intval($_GET['jtStartIndex']) && $cc < ($_GET['jtPageSize']*($_GET['jtStartIndex']+1)))
					{
						$jTableResult['Records'][] = array_merge(array('__connected__'=>'0'), $this->objectToArray($r));
					}
					$cc++;
				}
			}
			
			//file_put_contents('test.json', json_encode($jTableResult));
			
			$jTableResult['TotalRecordCount'] = $cc;
		}
		
		//
		return json_encode($jTableResult);
	}
	
	//crud.php?project=pim&actTemplate=table&object=static_countries&action=updateReference&objectId=1&referenceName=static_territories&referenceType=p&referenceId=2&connect=1
	public function updateReference()
	{
		// main object
		$o = $this->projectName . '\\' . $this->objectName;
		$obj = new $o();
		$item = $obj->Get($this->objectId);
		
		// reference-object
		require_once($this->ppath.'/objects/class.'.$this->referenceName.'.php');
		$or = $this->projectName . '\\' . $this->referenceName;
		$ref = new $or();
		$refitem = $ref->Get($this->referenceId);
		
		$action = false;
		switch($_GET['connect'])
		{
			case 0:
				$action = 'Remove'.$this->referenceName;
				$msg = 'disconnected';
			break;
			case 1:
				$action = 'Add'.$this->referenceName;
				$msg = 'connected';
			break;
		}
		if($action)
		{
			$item->$action($refitem);
			$item->Save();
			echo $msg;
		}
	}
	
	public function createSubContent ()
	{
		$jTableResult = array();
		$jTableResult['Result'] = 'ERROR';
		$jTableResult['Message'] = json_encode('not implemented atm');
		return json_encode($jTableResult);
	}
	
}

// init the class
$c = new table_crud();
?>
