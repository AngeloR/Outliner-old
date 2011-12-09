<?php
/**
 *	@class: 	OPCDataGrid
 * 	@author: 	Angelo Rodrigues
 *	@http:		http://wheremy.feethavebeen.com/projects/datagrid
 *	@version:	2.5
 *  @desc:
 *		The DataGrid class provides an easy interface to create
 *		tables using multi-dimensional arrays - most likely gathered
 *		from a database.
 *
 *		-Latest Update-
 *                  + added modification of rows - more useful that updating
 *
 *  5 rows, 39-base + 1 cols, norename colums, mysql.user @9.4641E-6 s-approx 10,000 iterations
 *	500 rows, 6-base + 1 cols, fullrename columns, mysql.help_topic @50s-approx 1,000 iterations
 *
 */

/**
 * OPCDataGrid provides an easy interface for generating tables from array-based
 * results.
 *
 * Generally, generating data-displays is a predominant part of building web
 * applications. Sometimes you get to tweak how the data is presented, but a lot
 * of times, you just need the data to be displayed as a table. The database
 * has all the values you need, but the field-names might not be user-friendly,
 * or you might not want the user to see all the fields.
 *
 * Previously, you would need to write your own loops to generate the table,
 * involving a lot of excess coding. OPCDataGrid allows you to pass in an array
 * of data, specify the rows you want to display (and even re-name them) and lets
 * you generate your table with minimum coding. 
 */
class OPCDataGrid {
    private $dataSource;
    private $displayFields;
    private $displaySource;
    private $modify;

    /**
     * Can set the datasource upon creation if you want
     * 
     * @param array $dataSource optional The multi-dimensional datasource
     */

    public function __construct(array $dataSource = array()) {
        if(count($dataSource) > 0) {
            $this->source($dataSource);
        }    
    }

    /**
     * Set the datasource
     * 
     * @param array $array  The datasource
     */
    public function source(array $array) {
        $this->dataSource = $array;
        $this->displaySource = array();
        $this->displayFields = array();
        $this->modify = array();
    }

    /**
     * Configure which fields will be displayed
     *
     * @param array $arrayFields    List of fields that will be displayed along
     *                                  with their display name
     */
    public function fields(array $arrayFields) {
        $this->displayFields = $arrayFields;
        foreach($this->dataSource as $i => $row) {
            foreach($arrayFields as $aField=>$aValue) {
                foreach($row as $field=>$value) {
                    if($field == $aField) {
                        $tmpArray[$field] = $value;
                    }
                }
            }
            if(count($tmpArray) > 0) {
                $this->displaySource[] = $tmpArray;
            }
	}
    }

    /**
     * A shortcut for calling addFieldAfter or addFieldBefore.
     *
     * @param string $fieldName     The new name of the field that will be displayed
     * @param string $safeName      The new internal name of the field
     * @param string $value         The value of the field
     * @param array $location       The location as an array('before||after'=>'field');
     */
    public function addField($fieldName,$safeName,$value,array $location) {
	$this->buildDisplayFields();
	if(array_key_exists('after',$location)) {
            $this->addFieldAfter($fieldName,$safeName,$value,$location['after']);
	}
	else if(array_key_exists('before',$location)) {
            $this->addFieldBefore($fieldName,$safeName,$value,$location['before']);
	}
	else {
            echo 'Failed, location does not exist';
        }
    }

    /**
     * Sets a field for modification on render.
     * 
     * @param string $fieldName     Field to apply the callback to
     * @param mixed $callback       A function (string|lambda) to execute on the field
     */
    public function modify($fieldName,$callback) {
        $this->modify[$fieldName] = $callback;
    }

    /**
     * Adds the new field before the specified $location field. If the value is
     * a reference to another column, it is parsed right away.
     *
     * @param string $fieldName     The new field name as it will be displayed
     * @param string $safeName      The new internal field name
     * @param string $value         The value of the field
     * @param string $location      Which field the new field will appear before
     */
    public function addFieldAfter($fieldName,$safeName,$value,$location) {
        foreach($this->displaySource as $i => $row) {
            if(array_key_exists($location,$row)) {
                $tmp = array();
                preg_match_all('/{.*?}/',$value,$out);
                $nVal = '';
                foreach($row as $field=>$val) {
                    if($location == $field) {
                        $tmp[$field] = $val;
			if(count($out[0]) > 0) {
                            if($nVal == '') $nVal = $value;
                            foreach($out[0] as $x => $match) {
                                $key = substr(substr($match,0,-1),1);
                                $nVal = str_replace($out[0][$x],$this->dataSource[$i][$key],$nVal);
                            }
                        }
			$tmp[$safeName] = $nVal;	
                        $nVal = '';
                    }
                    else {
                        $tmp[$field] = $val;
                    }
                }
                $this->displaySource[$i] = $tmp;
                $this->addDisplayField($safeName,$fieldName,array('after'=>$location));
            }
        }
    }

    /**
     * Adds the new field before the specified $location field. If the value is
     * a reference to another column, it is parsed right away.
     * 
     * @param string $fieldName     The new field name as it will be displayed
     * @param string $safeName      The new internal field name
     * @param string $value         The value of the field
     * @param string $location      Which field the new field will appear before
     */
    public function addFieldBefore($fieldName,$safeName,$value,$location) {
        foreach($this->displaySource as $i => $row) {
            if(array_key_exists($location,$row)) {
                $tmp = array();
                preg_match_all('/{.*?}/',$value,$out);
                $nVal = '';
                foreach($row as $field=>$val) {
                    if($location == $field) {
                        if(count($out[0]) > 0) {
                            if($nVal == '') $nVal = $value;
                            foreach($out[0] as $x => $match) {
                                $key = substr(substr($match,0,-1),1);
                                $nVal = str_replace($out[0][$x],$this->dataSource[$i][$key],$nVal);
                            }
                        }

                        $tmp[$safeName] = $nVal;
                        $nVal = '';
                        $tmp[$field] = $val;
                    }
                    else {
                        $tmp[$field] = $val;
                    }
                }
                $this->displaySource[$i] = $tmp;
                $this->addDisplayField($safeName,$fieldName,array('before'=>$location));
            }
        }
    }

    /**
     * Places the newly added field in the right location
     * 
     * @param string $safeName
     * @param string $fieldName
     * @param array $location
     */
    private function addDisplayField ($safeName,$fieldName,array $location) {
	if(array_key_exists('after',$location)) {
            $tmp = array();
            foreach($this->displayFields as $safe=>$actual) {
                if($safe == $location['after']) {
                    $tmp[$safe] = $actual;
                    $tmp[$safeName] = $fieldName;
		}
		else {
                    $tmp[$safe] = $actual;
		}
            }
            $this->displayFields = $tmp;
	}
	else if(array_key_exists('before',$location)) {
            $tmp = array();
            foreach($this->displayFields as $safe=>$actual) {
                if($safe == $location['before']) {
                    $tmp[$safeName] = $fieldName;
                    $tmp[$safe] = $actual;
		}
		else {
                    $tmp[$safe] = $actual;
		}
            }
            $this->displayFields = $tmp;
	}
	else {
            echo $location .' is not a valid location';
    	}
    }

    /**
     * Sets up the display source so that when we try and render a new field
     * we don't loop through every single field, just the necessary ones.
     */
    private function buildDisplayFields() {
	if(count($this->displayFields) < 1) {
            $row = $this->dataSource[0];
            foreach($row as $field=>$value) {
                $this->displayFields[$field] = $field;
            }
            $this->displaySource = $this->dataSource;
	}
    }

    /**
     * Echo's the table after it is built.
     */
    public function render() {
        echo $this->build();
    }

    /**
     * Builds the table and returns it as a string
     *
     * @return string   complete table
     */
    public function build() {
        $this->buildDisplayFields();
        $tmp = '<table class="datagrid zebra-striped" cellspacing="0">';
        $tmp .= $this->createTableHeaders();
				$tmp .= '<tbody>';
        foreach($this->displaySource as $i => $row) {
            $class = 'odd';
            if($i%2 != 0) {
                $class = 'even';
            }
            $tmp .= $this->addRowToTable($row,$class,$i);
        }
        $tmp .= '</tbody></table>';
        return $tmp;
    }

    /**
     * Builds a header row for the table based on the display fields
     * 
     * @return string   header row for table
     */
    private function createTableHeaders() {
        $tmp = '<tr><thead>';
        foreach($this->displayFields as $safe=>$actual) {
            $tmp .= '<th>'.$actual.'</th>';
        }
        $tmp .= '</thead></tr>';
        return $tmp;
    }

    /**
     * Generates a single row in the table, applying any field modifications
     * as defined.
     * 
     * @param array $row    the current row of display data
     * @param string $class even or odd class
     * @param int $index    the row index, used for passing a full row to modify()
     * @return string       the built table
     */
    private function addRowToTable($row,$class,$index) {
        $tmp = '<tr>';
        foreach($row as $field=>$val) {
            if(array_key_exists($field, $this->modify) && is_callable($this->modify[$field])) {
                $val = call_user_func($this->modify[$field],$val,$this->dataSource[$index]);
            }
            $tmp .= '<td class="'.$class.'">'.$val.'</td>';
        }
        $tmp .= '</tr>';
        return $tmp;
    }
}