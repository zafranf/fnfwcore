<?php
// include queries
if (file_exists(APP_PATH.'queries.php')) {
    include APP_PATH.'queries.php';
}

/**
 * [getConnectDB description]
 * @return [type] [description]
 */
if (!function_exists('getConnectDB')) {
    function getConnectDB() {
        $config = config();
        
        $db = $config['database'];
        $con = mysqli_connect($db['host'], $db['username'], $db['password'], $db['dbname']);

        return $con;
    }
}

/**
 * [doQuery description]
 * @param  string $query [description]
 * @return [type]        [description]
 */
if (!function_exists('query')) {
    function query($sql='', $action='', $table='') {
        /* Initiate Connection */
        $con = getConnectDB();

        /* Execute Query */
        $query = mysqli_query($con, $sql);

        /* Handle Query Error */
        if (mysqli_errno($con)) {
            throw new Exception(mysqli_error($con), 1);
        } 

        /* Check Action */
        if ($action=="") {
            $sqls = explode(" ", $sql);
            $action = strtolower($sqls[0]);
        }

        /* Handle Select Result */
        if ($action=="select") {
            $data = [];
            $rows = mysqli_num_rows($query);
            while ($res = mysqli_fetch_assoc($query)) {
                $data[] = $res;
            }
            $result = [
                'rows' => $rows,
                'data' => $data
            ];
        }
        /* Handle Insert Result */
        else if ($action=="insert") {
            /* Get Inserted ID */
            $id = mysqli_insert_id($con);

            /* Get Inserted ID if Insert More Than One Record */
            $affected = $con->affected_rows;
            if ($affected>1) {
                $field_id = '';
                $columns = query("SHOW COLUMNS FROM ".$table, "select");
                foreach ($columns['data'] as $col) {
                    if ($col['Extra']=="auto_increment") {
                        $field_id = $col['Field'];
                    }
                }
                if ($field_id!='') {
                    $latest = getDataOne($table, [
                            'select' => $field_id,
                            'order'  => [$field_id, 'desc']
                        ]);
                    $last_id = $latest['data']['id'];

                    $ids = [];
                    for ($i=$id; $i<=$last_id; $i++) { 
                        $ids[] = $i;
                    }
                    $id = $ids;
                }
            }

            $result = $id;
        } 
        /* Handle Other Action */
        else {
            $result = $query;
        }

        /* Close Connection */
        mysqli_close($con);

        return $result;
    }
}

/**
 * [getData description]
 * @param  string $table  [description]
 * @param  array  $params [description]
 * @return [type]         [description]
 */
if (!function_exists('getData')) {
    function getData($table='', $params=[], $debug=false) {
        /* Check Table */
        if ($table=="") {
            throw new Exception("TABLE not selected", 1);
        }

        /* Set Query Select */
        $select = setQuerySelect($params);

        /* Set Query Table */
        $table = setQueryTable($table);

        /* Set Query Join */
        $join = setQueryJoin($params);

        /* Set Query Where */
        $where = setQueryWhere($params);

        /* Set Query Group */
        $group = setQueryGroup($params);

        /* Set Query Order */
        $order = setQueryOrder($params);

        /* Set Query Limit */
        $limit = setQueryLimit($params);

        /* Set Query Offset */
        $offset = setQueryOffset($params);

        /* Combine Query */
        $sql = "SELECT ".$select." \nFROM ".$table."".
                $join.
                $where.
                $group.
                $order.
                $limit.
                $offset;

        /* Print debug */
        if ($debug) {
            debug($sql);
        }

        return query($sql, "select");
    }
}

/**
 * [getDataOne description]
 * @param  string $table  [description]
 * @param  array  $params [description]
 * @return [type]         [description]
 */
if (!function_exists('getDataOne')) {
    function getDataOne($table='', $params=[], $debug=false) {
        /* Set Limit to 1 as Default */
        $params['limit'] = 1;

        /* Execute Query with getData() */
        $result = getData($table, $params, $debug);

        return [
            'rows' => $result['rows'],
            'data' => ($result['rows']>0)?$result['data'][0]:[]
        ];
    }
}

/**
 * [getDataPagination description]
 * @param  string $table  [description]
 * @param  array  $params [description]
 * @return [type]         [description]
 */
if (!function_exists('getDataPagination')) {
    function getDataPagination($table="", $params=[], $debug=false) {
        /* Check Page No. */
        $page = isset($_GET['page'])?_input('page', true):1;
        if ($page<1) {
            $page = 1;
        }

        /* Get Main Data */
        if (!isset($params['limit'])) {
            $params['limit'] = config('data_perpage');
        }
        $params['offset'] = ($page-1) * $params['limit'];
        $data = getData($table, $params, $debug);

        /* Get All Data */
        $limit = $params['limit'];
        $number = $params['offset'];
        unset($params['limit']);
        unset($params['offset']);
        $data_all = getData($table, $params, $debug);

        /* Set Detail Pagination */
        $data['total'] = $data_all['rows'];
        $data['perpage'] = $limit;
        $data['last_page'] = ceil($data_all['rows']/$limit);
        $data['current_page'] = $page;
        $data['prev_page_link'] = null;
        $data['next_page_link'] = null;
        $data['number'] = $number+1;

        /* Set Previous and Next Link */
        if ($page>1) {
            $data['prev_page_link'] = url('?page='.($page-1), true);
        }
        if ($page<$data['last_page']) {
            $data['next_page_link'] = url('?page='.($page+1), true);
        }

        return $data;
    }
}

/**
 * [insertData description]
 * @param  string $table  [description]
 * @param  array  $values [description]
 * @return [type]         [description]
 */
if (!function_exists('insertData')) {
    function insertData($table='', $values=[], $debug=false) {
        /* Check Table */
        if ($table=="") {
            throw new Exception("TABLE not selected", 1);
        }
        /* Check Values */
        if (empty($values)) {
            throw new Exception("VALUES not set correctly", 1);
        }

        /* Grouping Field and Values */
        if (isset($values[0]) && is_array($values[0])) {
            foreach ($values as $key => $value) {
                $fields = "`".implode("`, `", array_keys($value))."`";
                $vals[] = "'".implode("', '", array_values($value))."'";
            }
            $vals = implode("), (", $vals);
        } else {
            $fields = "`".implode("`, `", array_keys($values))."`";
            $vals = "'".implode("', '", array_values($values))."'";
        }

        /* Combine Query */
        $sql = "INSERT INTO `".$table."` (".$fields.") VALUES (".$vals.")";

        /* Print debug */
        if ($debug) {
            debug($sql);
        }

        return query($sql, "insert", $table);
    }
}

/**
 * [updateData description]
 * @param  string $table  [description]
 * @param  array  $values [description]
 * @param  array  $wheres [description]
 * @return [type]         [description]
 */
if (!function_exists('updateData')) {
    function updateData($table='', $values=[], $wheres=[], $debug=false) {
        /* Check Table */
        if ($table=="") {
            throw new Exception("TABLE not selected", 1);
        }
        /* Check Values */
        if (empty($values)) {
            throw new Exception("VALUES not set correctly", 1);
        }

        /* Set Query SET */
        $sets = [];
        foreach ($values as $key => $val) {
            $sets[] = "`".$key."`='".$val."'";
        }
        $sets = implode(", ", $sets);

        /* Set Query Where */
        $where = setQueryWhere($wheres);

        /* Combine Query */
        $sql = "UPDATE `".$table."` SET ".$sets.$where;

        /* Print debug */
        if ($debug) {
            debug($sql);
        }

        return query($sql, "update");
    }
}

/**
 * [insertOrUpdateData description]
 * @param  string $table  [description]
 * @param  array  $values [description]
 * @param  array  $wheres [description]
 * @return [type]         [description]
 */
if (!function_exists('insertOrUpdateData')) {
    function insertOrUpdateData($table='', $values=[], $wheres=[]) {
        $save = false;

        /* Check Data */
        $check = getDataOne($table, $wheres);

        /* Update if exists */
        if ($check['rows']) {
            $save = updateData($table, $values, $wheres);
        } 
        /* Insert new one */
        else {
            $save = insertData($table, $values);
        }

        return $save;
    }
}

/**
 * [deleteData description]
 * @param  string $table  [description]
 * @param  array  $wheres [description]
 * @return [type]         [description]
 */
if (!function_exists('deleteData')) {
    function deleteData($table='', $wheres=[], $debug=false) {
        /* Check Table */
        if ($table=="") {
            throw new Exception("TABLE not selected", 1);
        }

        /* Set Query Where */
        $where = setQueryWhere($wheres);

        /* Combine Query */
        $sql = "DELETE FROM `".$table."`".$where;

        /* Print debug */
        if ($debug) {
            debug($sql);
        }
        
        return query($sql, 'delete');
    }
}

/**
 * [setQuerySelect description]
 * @param array $params [description]
 * @return [type]       [description]
 */
if (!function_exists('setQuerySelect')) {
    function setQuerySelect($params=[]) {
        /* Set Query Select */
        $select = "*";
        if (isset($params['select'])) {
            $selects = $params['select'];
            if (!is_array($selects)) {
                $xselects = explode(",", $selects);
                $selects = array_map(function($arr){
                    $arr = trim($arr);
                    $ar = $arr;
                    /* check space */
                    $space = preg_match("/( )/", $arr);
                    if ($space) {
                        $ar = '';
                        $xarr = explode(" ", $arr);
                        foreach ($xarr as $n => $varr) {
                            /* check function */
                            $fn = preg_match("/(\([a-zA-Z0-9.]+\))/", $varr);
                            if (!$fn && $varr!="AS" && $varr!="as") {
                                /* add AS if not set */
                                if ($n==1) {
                                    $ar .= " AS ";
                                }

                                /* add ` if not wildcard */
                                if ($varr!="*") {
                                    $ar .= "`".$varr."`";
                                }
                            } else {
                                /* add space if AS */
                                if ($varr=="AS" || $varr=="as") {
                                    $ar .= " ".strtoupper($varr)." ";
                                } else {
                                    $ar .= $varr;
                                }
                            }
                        }
                    } else {
                        /* check function */
                        $fn = preg_match("/(\([a-zA-Z0-9.]+\))/", $arr);
                        if ($fn) {
                            $ar = $arr;
                        } else {
                            $ar = '';
                            $xarr = explode(".", $arr);
                            foreach ($xarr as $n => $varr) {
                                /* add dot */
                                if ($n==1) {
                                    $ar .= ".";
                                }

                                /* add ` if not wildcard */
                                if ($varr!="*"){
                                    $ar .= "`".$varr."`";
                                } else {
                                    $ar .= $varr;
                                }
                            }
                        }
                    }
                    
                    return trim($ar);
                }, $xselects);
            }
            $select = "".implode(", ", $selects)."";
        }

        /* Set Query Select Distinct */
        if (isset($params['select_distinct'])) {
            if ($params['select_distinct']!=false && $params['select_distinct']!==true) {
                $params['select'] = $params['select_distinct'];
                unset($params['select_distinct']);
                $select = "DISTINC ".setQuerySelect($params);
            } else if ($params['select_distinct']==true) {
                $select = "DISTINC ".$select;
            }
        }

        /* Set Query Select top */
        if (isset($params['select_top'])) {
            $n = $params['select_top'];
            if (substr($params['select_top'], -1)=="%") {
                $n = substr($params['select_top'], 0, -1)." PERCENT";
            }
            unset($params['select_top']);
            $select = "TOP ".$n." ".setQuerySelect($params);
        }

        return $select;
    }
}

/**
 * [setQueryTable description]
 * @param array $params [description]
 * @return [type]       [description]
 */
if (!function_exists('setQueryTable')) {
    function setQueryTable($table) {
        /* Set Query Table */
        if (isset($table)) {
            $tables = $table;
            if (!is_array($tables)) {
                $tables = explode(",", $tables);
            }
            $tables = array_map(function($arr){
                $arr = trim($arr);
                $xtbl = explode(" ", $arr);
                $atbl = '';
                foreach ($xtbl as $n => $vtbl) {
                    /* add as */
                    if ($vtbl!="AS" && $vtbl!="as"){
                        if ($n==1 || $n==2) {
                            $atbl .= " AS ";
                        }
                        $atbl .= "`".trim($vtbl)."`";
                    }
                }

                return trim($atbl);
            }, $tables);
            $table = "".implode(", ", $tables)."";
        }

        return $table;
    }
}

/**
 * [setQueryWhere description]
 * @param array $params [description]
 * @return [type]       [description]
 */
if (!function_exists('setQueryWhere')) {
    function setQueryWhere($params=[]) {
        /* Set Query Where */
        $where = "";
        if (isset($params['where'])) {
            $wheres = [];
            foreach ($params['where'] as $key => $val) {
                if (is_array($val)) {
                    if (count($val)==3) {
                        $xval = explode(".", $val[0]);
                        $aval = '';
                        foreach ($xval as $n => $vval) {
                            /* add dot */
                            if ($n==1) {
                                $aval .= ".";
                            }
                            $aval .= "`".$vval."`";
                        }

                        /* check value null */
                        if ($val[2]===null) {
                            if ($val[1]=="=") {
                                $val[1] = "IS";
                            } else if ($val[1]=="!=" || strtoupper($val[1])=="NOT") {
                                $val[1] = "IS NOT";
                            }

                            $wheres[] = $aval." ".$val[1]." NULL";
                        } else {
                            $wheres[] = $aval." ".strtoupper($val[1])." '".$val[2]."'";
                        }
                    } else if (count($val)==2) {
                        $xval = explode(".", $val[0]);
                        $aval = '';
                        foreach ($xval as $n => $vval) {
                            /* add dot */
                            if ($n==1) {
                                $aval .= ".";
                            }
                            $aval .= "`".$vval."`";
                        }

                        /* check value null */
                        if ($val[1]===null) {
                            $wheres[] = $aval." IS NULL";
                        } else {
                            $wheres[] = $aval."='".$val[1]."'";
                        }
                    } else {
                        throw new Exception("WHERE parameter not set correctly", 1);
                    }
                } else {
                    $xkey = explode(".", $key);
                    $akey = '';
                    foreach ($xkey as $n => $vkey) {
                        /* add dot */
                        if ($n==1) {
                            $akey .= ".";
                        }
                        $akey .= "`".$vkey."`";
                    }

                    /* check value null */
                    if ($val===null) {
                        $wheres[] = $akey." IS NULL";
                    } else {
                        $wheres[] = $akey."='".$val."'";
                    }
                }
            }
            $where = " \nWHERE ".implode(" \n    AND ", $wheres);
        }

        /* Set Query Where Not */
        $where_not = "";
        if (isset($params['where_not'])) {
            $wheres = [];
            foreach ($params['where_not'] as $key => $val) {
                if (is_array($val)) {
                    if (count($val)==3) {
                        $xval = explode(".", $val[0]);
                        $aval = '';
                        foreach ($xval as $n => $vval) {
                            /* add dot */
                            if ($n==1) {
                                $aval .= ".";
                            }
                            $aval .= "`".$vval."`";
                        }
                        $wheres[] = $aval." ".strtoupper($val[1])." '".$val[2]."'";
                    } else if (count($val)==2) {
                        $xval = explode(".", $val[0]);
                        $aval = '';
                        foreach ($xval as $n => $vval) {
                            /* add dot */
                            if ($n==1) {
                                $aval .= ".";
                            }
                            $aval .= "`".$vval."`";
                        }
                        $wheres[] = $aval."='".$val[1]."'";
                    } else {
                        throw new Exception("WHERE parameter not set correctly", 1);
                    }
                } else {
                    $xkey = explode(".", $key);
                    $akey = '';
                    foreach ($xkey as $n => $vkey) {
                        /* add dot */
                        if ($n==1) {
                            $akey .= ".";
                        }
                        $akey .= "`".$vkey."`";
                    }
                    $wheres[] = $akey."='".$val."'";
                }
            }
            $where_not = implode(" \n    AND NOT ", $wheres);

            /* join with other where clause */
            $concat = ($where!="")?" \n    AND NOT ":" \nWHERE NOT ";
            $where .= $concat.$where_not;
        }

        /* Set Query OR Where */
        $where_or = '';
        if (isset($params['or_where'])) {
            $wheres = [];
            foreach ($params['or_where'] as $key => $val) {
                if (is_array($val)) {
                    if (count($val)==3) {
                        $xval = explode(".", $val[0]);
                        $aval = '';
                        foreach ($xval as $n => $vval) {
                            /* add dot */
                            if ($n==1) {
                                $aval .= ".";
                            }
                            $aval .= "`".$vval."`";
                        }
                        $wheres[] = $aval." ".$val[1]." '".$val[2]."'";
                    } else if (count($val)==2) {
                        $xval = explode(".", $val[0]);
                        $aval = '';
                        foreach ($xval as $n => $vval) {
                            /* add dot */
                            if ($n==1) {
                                $aval .= ".";
                            }
                            $aval .= "`".$vval."`";
                        }

                        /* check value null */
                        if ($val[1]===null) {
                            $wheres[] = $aval." IS NULL";
                        } else {
                            $wheres[] = $aval."='".$val[1]."'";
                        }
                    } else {
                        throw new Exception("OR WHERE parameter not set correctly", 1);
                    }
                } else {
                    $xkey = explode(".", $key);
                    $akey = '';
                    foreach ($xkey as $n => $vkey) {
                        /* add dot */
                        if ($n==1) {
                            $akey .= ".";
                        }
                        $akey .= "`".$vkey."`";
                    }

                    /* check value null */
                    if ($val===null) {
                        $wheres[] = $akey." IS NULL";
                    } else {
                        $wheres[] = $akey."='".$val."'";
                    }
                }
            }
            $where_or = implode(" \n    OR ", $wheres);

            /* join with other where clause */
            $concat = ($where!="")?" \n    OR ":" \nWHERE ";
            $where .= $concat.$where_or;
        }

        /* Set Query OR Where AND */
        $where_or_and = '';
        if (isset($params['or_where_and'])) {
            $wheres = [];
            foreach ($params['or_where_and'] as $key => $val) {
                if (is_array($val)) {
                    if (count($val)==3) {
                        $xval = explode(".", $val[0]);
                        $aval = '';
                        foreach ($xval as $n => $vval) {
                            /* add dot */
                            if ($n==1) {
                                $aval .= ".";
                            }
                            $aval .= "`".$vval."`";
                        }
                        $wheres[] = $aval." ".$val[1]." '".$val[2]."'";
                    } else if (count($val)==2) {
                        $xval = explode(".", $val[0]);
                        $aval = '';
                        foreach ($xval as $n => $vval) {
                            /* add dot */
                            if ($n==1) {
                                $aval .= ".";
                            }
                            $aval .= "`".$vval."`";
                        }

                        /* check value null */
                        if ($val[1]===null) {
                            $wheres[] = $aval." IS NULL";
                        } else {
                            $wheres[] = $aval."='".$val[1]."'";
                        }
                    } else {
                        throw new Exception("OR WHERE parameter not set correctly", 1);
                    }
                } else {
                    $xkey = explode(".", $key);
                    $akey = '';
                    foreach ($xkey as $n => $vkey) {
                        /* add dot */
                        if ($n==1) {
                            $akey .= ".";
                        }
                        $akey .= "`".$vkey."`";
                    }

                    /* check value null */
                    if ($val===null) {
                        $wheres[] = $akey." IS NULL";
                    } else {
                        $wheres[] = $akey."='".$val."'";
                    }
                }
            }
            $where_or_and = "(".implode(" AND ", $wheres).")";

            /* join with other where clause */
            $concat = ($where!="")?" \n    OR ":" \nWHERE ";
            $where .= $concat.$where_or_and;
        }

        /* Set Query AND Where OR */
        $where_and_or = '';
        if (isset($params['and_where_or'])) {
            $wheres = [];
            foreach ($params['and_where_or'] as $key => $val) {
                if (is_array($val)) {
                    if (count($val)==3) {
                        $xval = explode(".", $val[0]);
                        $aval = '';
                        foreach ($xval as $n => $vval) {
                            /* add dot */
                            if ($n==1) {
                                $aval .= ".";
                            }
                            $aval .= "`".$vval."`";
                        }
                        $wheres[] = $aval." ".$val[1]." '".$val[2]."'";
                    } else if (count($val)==2) {
                        $xval = explode(".", $val[0]);
                        $aval = '';
                        foreach ($xval as $n => $vval) {
                            /* add dot */
                            if ($n==1) {
                                $aval .= ".";
                            }
                            $aval .= "`".$vval."`";
                        }

                        /* check value null */
                        if ($val[1]===null) {
                            $wheres[] = $aval." IS NULL";
                        } else {
                            $wheres[] = $aval."='".$val[1]."'";
                        }
                    } else {
                        throw new Exception("OR WHERE parameter not set correctly", 1);
                    }
                } else {
                    $xkey = explode(".", $key);
                    $akey = '';
                    foreach ($xkey as $n => $vkey) {
                        /* add dot */
                        if ($n==1) {
                            $akey .= ".";
                        }
                        $akey .= "`".$vkey."`";
                    }

                    /* check value null */
                    if ($val===null) {
                        $wheres[] = $akey." IS NULL";
                    } else {
                        $wheres[] = $akey."='".$val."'";
                    }
                }
            }
            $where_and_or = "(".implode(" OR ", $wheres).")";

            /* join with other where clause */
            $concat = ($where!="")?" \n    AND ":" \nWHERE ";
            $where .= $concat.$where_and_or;
        }

        /* Set Query Or Where Not */
        $or_where_not = "";
        if (isset($params['or_where_not'])) {
            $wheres = [];
            foreach ($params['or_where_not'] as $key => $val) {
                if (is_array($val)) {
                    if (count($val)==3) {
                        $xval = explode(".", $val[0]);
                        $aval = '';
                        foreach ($xval as $n => $vval) {
                            /* add dot */
                            if ($n==1) {
                                $aval .= ".";
                            }
                            $aval .= "`".$vval."`";
                        }
                        $wheres[] = $aval." ".strtoupper($val[1])." '".$val[2]."'";
                    } else if (count($val)==2) {
                        $xval = explode(".", $val[0]);
                        $aval = '';
                        foreach ($xval as $n => $vval) {
                            /* add dot */
                            if ($n==1) {
                                $aval .= ".";
                            }
                            $aval .= "`".$vval."`";
                        }
                        $wheres[] = $aval."='".$val[1]."'";
                    } else {
                        throw new Exception("WHERE parameter not set correctly", 1);
                    }
                } else {
                    $xkey = explode(".", $key);
                    $akey = '';
                    foreach ($xkey as $n => $vkey) {
                        /* add dot */
                        if ($n==1) {
                            $akey .= ".";
                        }
                        $akey .= "`".$vkey."`";
                    }
                    $wheres[] = $akey."='".$val."'";
                }
            }
            $or_where_not = implode(" \n    OR NOT ", $wheres);

            /* join with other where clause */
            $concat = ($where!="")?" \n    OR NOT ":" \nWHERE ";
            $where .= $concat.$or_where_not;
        }

        /* Set Query Where Like */
        $where_like = "";
        if (isset($params['where_like'])) {
            $wheres = [];
            foreach ($params['where_like'] as $key => $val) {
                if (is_array($val)) {
                    if (count($val)==2) {
                        $xval = explode(".", $val[0]);
                        $aval = '';
                        foreach ($xval as $n => $vval) {
                            /* add dot */
                            if ($n==1) {
                                $aval .= ".";
                            }
                            $aval .= "`".$vval."`";
                        }
                        $wheres[] = $aval." LIKE '".$val[1]."'";
                    } else {
                        throw new Exception("WHERE parameter not set correctly", 1);
                    }
                } else {
                    $xkey = explode(".", $key);
                    $akey = '';
                    foreach ($xkey as $n => $vkey) {
                        /* add dot */
                        if ($n==1) {
                            $akey .= ".";
                        }
                        $akey .= "`".$vkey."`";
                    }
                    $wheres[] = $akey." LIKE '".$val."'";
                }
            }
            $where_like = implode(" \n    AND ", $wheres);

            /* join with other where clause */
            $concat = ($where!="")?" \n    AND ":" \nWHERE ";
            $where .= $concat.$where_like;
        }

        /* Set Query Or Where Like */
        $or_where_like = "";
        if (isset($params['or_where_like'])) {
            $wheres = [];
            foreach ($params['or_where_like'] as $key => $val) {
                if (is_array($val)) {
                    if (count($val)==2) {
                        $xval = explode(".", $val[0]);
                        $aval = '';
                        foreach ($xval as $n => $vval) {
                            /* add dot */
                            if ($n==1) {
                                $aval .= ".";
                            }
                            $aval .= "`".$vval."`";
                        }
                        $wheres[] = $aval." LIKE '".$val[1]."'";
                    } else {
                        throw new Exception("WHERE parameter not set correctly", 1);
                    }
                } else {
                    $xkey = explode(".", $key);
                    $akey = '';
                    foreach ($xkey as $n => $vkey) {
                        /* add dot */
                        if ($n==1) {
                            $akey .= ".";
                        }
                        $akey .= "`".$vkey."`";
                    }
                    $wheres[] = $akey." LIKE '".$val."'";
                }
            }
            $or_where_like = implode(" \n    OR ", $wheres);

            /* join with other where clause */
            $concat = ($where!="")?" \n    OR ":" \nWHERE ";
            $where .= $concat.$or_where_like;
        }

        /* Set Query Where Not Like */
        $where_not_like = "";
        if (isset($params['where_not_like'])) {
            $wheres = [];
            foreach ($params['where_not_like'] as $key => $val) {
                if (is_array($val)) {
                    if (count($val)==2) {
                        $xval = explode(".", $val[0]);
                        $aval = '';
                        foreach ($xval as $n => $vval) {
                            /* add dot */
                            if ($n==1) {
                                $aval .= ".";
                            }
                            $aval .= "`".$vval."`";
                        }
                        $wheres[] = $aval." NOT LIKE '".$val[1]."'";
                    } else {
                        throw new Exception("WHERE parameter not set correctly", 1);
                    }
                } else {
                    $xkey = explode(".", $key);
                    $akey = '';
                    foreach ($xkey as $n => $vkey) {
                        /* add dot */
                        if ($n==1) {
                            $akey .= ".";
                        }
                        $akey .= "`".$vkey."`";
                    }
                    $wheres[] = $akey." NOT LIKE '".$val."'";
                }
            }
            $where_not_like = implode(" \n    AND ", $wheres);

            /* join with other where clause */
            $concat = ($where!="")?" \n    AND ":" \nWHERE ";
            $where .= $concat.$where_not_like;
        }

        /* Set Query Where Between */
        $where_bw = "";
        if (isset($params['where_between'])) {
            $wheres = [];
            foreach ($params['where_between'] as $field => $vals) {
                $xfield = explode(".", $field);
                $afield = '';
                foreach ($xfield as $n => $vfield) {
                    /* add dot */
                    if ($n==1) {
                        $afield .= ".";
                    }
                    $afield .= "`".$vfield."`";
                }
                $wheres[] = "(".$afield." BETWEEN '".$vals[0]."' AND '".$vals[1]."')";
            }
            $where_bw = implode(" \n    AND ", $wheres);

            /* join with other where clause */
            $concat = ($where!="")?" \n    AND ":" \nWHERE ";
            $where .= $concat.$where_bw;
        }

        /* Set Query Where Not Between */
        $where_not_bw = "";
        if (isset($params['where_not_between'])) {
            $wheres = [];
            foreach ($params['where_not_between'] as $field => $vals) {
                $xfield = explode(".", $field);
                $afield = '';
                foreach ($xfield as $n => $vfield) {
                    /* add dot */
                    if ($n==1) {
                        $afield .= ".";
                    }
                    $afield .= "`".$vfield."`";
                }
                $wheres[] = "(".$afield." NOT BETWEEN '".$vals[0]."' AND '".$vals[1]."')";
            }
            $where_not_bw = implode(" \n    AND ", $wheres);

            /* join with other where clause */
            $concat = ($where!="")?" \n    AND ":" \nWHERE ";
            $where .= $concat.$where_not_bw;
        }

        /* Set Query Or Where Between */
        $where_bw_or = "";
        if (isset($params['or_where_between'])) {
            $wheres = [];
            foreach ($params['or_where_between'] as $field => $vals) {
                $xfield = explode(".", $field);
                $afield = '';
                foreach ($xfield as $n => $vfield) {
                    /* add dot */
                    if ($n==1) {
                        $afield .= ".";
                    }
                    $afield .= "`".$vfield."`";
                }
                $wheres[] = "(".$afield." BETWEEN '".$vals[0]."' AND '".$vals[1]."')";
            }
            $where_bw_or = implode(" \n    OR ", $wheres);

            /* join with other where clause */
            $concat = ($where!="")?" \n    OR ":" \nWHERE ";
            $where .= $concat.$where_bw_or;
        }

        /* Set Query Or Where Between */
        $where_bw_or_not = "";
        if (isset($params['or_not_where_between'])) {
            $wheres = [];
            foreach ($params['or_not_where_between'] as $field => $vals) {
                $xfield = explode(".", $field);
                $afield = '';
                foreach ($xfield as $n => $vfield) {
                    /* add dot */
                    if ($n==1) {
                        $afield .= ".";
                    }
                    $afield .= "`".$vfield."`";
                }
                $wheres[] = "(".$afield." NOT BETWEEN '".$vals[0]."' AND '".$vals[1]."')";
            }
            $where_bw_or_not = implode(" \n    OR ", $wheres);

            /* join with other where clause */
            $concat = ($where!="")?" \n    OR ":" \nWHERE ";
            $where .= $concat.$where_bw_or_not;
        }

        /* Set Query Where Between */
        $where_in = "";
        if (isset($params['where_in'])) {
            $wheres = [];
            foreach ($params['where_in'] as $field => $vals) {
                $xfield = explode(".", $field);
                $afield = '';
                foreach ($xfield as $n => $vfield) {
                    /* add dot */
                    if ($n==1) {
                        $afield .= ".";
                    }
                    $afield .= "`".trim($vfield)."`";
                }
                $wheres[] = $afield." IN ('".implode("', '", $vals)."')";
            }
            $where_in = implode(" \n    AND ", $wheres);

            /* join with other where clause */
            $concat = ($where!="")?" \n    AND ":" \nWHERE ";
            $where .= $concat.$where_in;
        }

        /* Set Query Where Between */
        $where_not_in = "";
        if (isset($params['where_not_in'])) {
            $wheres = [];
            foreach ($params['where_not_in'] as $field => $vals) {
                $xfield = explode(".", $field);
                $afield = '';
                foreach ($xfield as $n => $vfield) {
                    /* add dot */
                    if ($n==1) {
                        $afield .= ".";
                    }
                    $afield .= "`".trim($vfield)."`";
                }
                $wheres[] = $afield." NOT IN ('".implode("', '", $vals)."')";
            }
            $where_not_in = implode(" \n    AND ", $wheres);

            /* join with other where clause */
            $concat = ($where!="")?" \n    AND ":" \nWHERE ";
            $where .= $concat.$where_not_in;
        }

        return $where;
    }
}

/**
 * [setQueryJoin description]
 * @param array $params [description]
 * @return [type]       [description]
 */
if (!function_exists('setQueryJoin')) {
    function setQueryJoin($params=[]) {
        /* Set Query Join */
        $join = "";
        if (isset($params['join'])) {
            $joins = [];
            foreach ($params['join'] as $tbl => $vals) {
                /* set table */
                $xtbl = explode(" ", $tbl);
                $atbl = '';
                foreach ($xtbl as $n => $vtbl) {
                    /* add as */
                    if ($vtbl!="AS" && $vtbl!="as"){
                        if ($n==1 || $n==2) {
                            $atbl .= " AS ";
                        }
                        $atbl .= "`".trim($vtbl)."`";
                    }
                }
                /* set val0 */
                $xval0 = explode(".", $vals[0]);
                $aval0 = '';
                foreach ($xval0 as $n => $vval0) {
                    /* add dot */
                    if ($n==1) {
                        $aval0 .= ".";
                    }
                    $aval0 .= "`".trim($vval0)."`";
                }
                /* set val1 */
                $xval1 = explode(".", $vals[1]);
                $aval1 = '';
                foreach ($xval1 as $n => $vval1) {
                    /* add dot */
                    if ($n==1) {
                        $aval1 .= ".";
                    }
                    $aval1 .= "`".trim($vval1)."`";
                }
                $joins[] = $atbl." \n    ON ".$aval0."=".$aval1;
            }
            $join = " JOIN ".implode(", ", $joins);
        }

        /* Set Query Inner Join */
        if (isset($params['inner_join'])) {
            $arrjoin = $params['inner_join'];
            foreach ($arrjoin as $key => $val) {
                $join .= "\nINNER".setQueryJoin([
                    'join' => [$key => $val]
                ]);
            }
        }

        /* Set Query Left Join */
        if (isset($params['left_join'])) {
            $arrjoin = $params['left_join'];
            foreach ($arrjoin as $key => $val) {
                $join .= "\nLEFT".setQueryJoin([
                    'join' => [$key => $val]
                ]);
            }
        }

        /* Set Query Left Outer Join */
        if (isset($params['left_outer_join'])) {
            $arrjoin = $params['left_outer_join'];
            foreach ($arrjoin as $key => $val) {
                $join .= "\nLEFT OUTER".setQueryJoin([
                    'join' => [$key => $val]
                ]);
            }
        }

        /* Set Query Right Join */
        if (isset($params['right_join'])) {
            $arrjoin = $params['right_join'];
            foreach ($arrjoin as $key => $val) {
                $join .= "\nRIGHT".setQueryJoin([
                    'join' => [$key => $val]
                ]);
            }
        }

        /* Set Query Right Outer Join */
        if (isset($params['right_outer_join'])) {
            $arrjoin = $params['right_outer_join'];
            foreach ($arrjoin as $key => $val) {
                $join .= "\nRIGHT OUTER".setQueryJoin([
                    'join' => [$key => $val]
                ]);
            }
        }

        /* Set Query Full Join */
        if (isset($params['full_join'])) {
            $arrjoin = $params['full_join'];
            foreach ($arrjoin as $key => $val) {
                $join .= "\nFULL".setQueryJoin([
                    'join' => [$key => $val]
                ]);
            }
        }

        /* Set Query Full Outer Join */
        if (isset($params['full_outer_join'])) {
            $arrjoin = $params['full_outer_join'];
            foreach ($arrjoin as $key => $val) {
                $join .= "\nFULL OUTER".setQueryJoin([
                    'join' => [$key => $val]
                ]);
            }
        }

        return $join;
    }
}

/**
 * [setQueryGroup description]
 * @param array $params [description]
 * @return [type]       [description]
 */
if (!function_exists('setQueryGroup')) {
    function setQueryGroup($params=[]) {
        /* Set Query Group */
        $group = "";
        if (isset($params['group'])) {
            $groups = $params['group'];
            if (!is_array($groups)) {
                $xgroups = explode(",", $groups);
                $groups = array_map(function($arr){
                    $ar = '';
                    $arr = trim($arr);
                    $xarr = explode(".", $arr);
                    foreach ($xarr as $n => $varr) {
                        /* add dot */
                        if ($n==1) {
                            $ar .= ".";
                        }

                        /* add ` if not wildcard */
                        if ($varr!="*"){
                            $ar .= "`".$varr."`";
                        } else {
                            $ar .= $varr;
                        }
                    }

                    return $ar;
                }, $xgroups);
            }
            $group = " \nGROUP BY ".implode(", ", $groups);
        }

        return $group;
    }
}

/**
 * [setQueryOrder description]
 * @param array $params [description]
 * @return [type]       [description]
 */
if (!function_exists('setQueryOrder')) {
    function setQueryOrder($params=[]) {
        /* Set Query Order */
        $order = "";
        if (isset($params['order'])) {
            $orders = [];
            foreach ($params['order'] as $key => $val) {
                if (is_array($val)) {
                    if (count($val)==2) {
                        $orders[] = "`".$val[0]."` ".strtoupper($val[1]);
                    } else {
                        throw new Exception("ORDER parameter not set correctly", 1);
                    }
                } else {
                    $xkey = explode(".", $key);
                    $akey = '';
                    foreach ($xkey as $n => $vkey) {
                        /* add dot */
                        if ($n==1) {
                            $akey .= ".";
                        }
                        $akey .= "`".$vkey."`";
                    }
                    $orders[] = $akey." ".strtoupper($val);
                }
            }
            $order = " \nORDER BY ".implode(", ", $orders);
        }

        return $order;
    }
}

/**
 * [setQueryLimit description]
 * @param array $params [description]
 * @return [type]       [description]
 */
if (!function_exists('setQueryLimit')) {
    function setQueryLimit($params=[]) {
        /* Set Query Limit */
        $limit = "";
        if (isset($params['limit'])) {
            $xlimit = explode(",", $params['limit']);
            if (count($xlimit)==2) {
                $limit = " \nLIMIT ".trim($xlimit[1])." OFFSET ".trim($xlimit[0]);
            } else {
                $limit = " \nLIMIT ".$params['limit'];
            }
        }

        return $limit;
    }
}

/**
 * [setQueryOffset description]
 * @param array $params [description]
 * @return [type]       [description]
 */
if (!function_exists('setQueryOffset')) {
    function setQueryOffset($params=[]) {
        /* Set Query Offset */
        $offset = "";
        if (isset($params['offset'])) {
            $offset = " \nOFFSET ".$params['offset'];
        }

        return $offset;
    }
}