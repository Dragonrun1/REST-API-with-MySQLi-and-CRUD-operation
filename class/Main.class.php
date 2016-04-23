<?php
@include('../config.php');
/**
 * Class Main
 */
class Main
{
    /**
     * Main constructor.
     */
    public function __construct()
    {
        /*BEGIN SETTING PAGE PER RECORD */
        if (isset($_GET['record']) && is_numeric($_GET['record'])) {
            $_SESSION['pagerecords_limit'] = $_GET['record'];
        }
        /* END SETTING PAGE PER RECORD */
        $this->pagefilename = strtolower(basename($_SERVER['PHP_SELF']));
        $info_array = [];
        $this->sitedata = $this->GetSingleRecord('site_settings', $info_array);
        define('SITE_TITLE', stripslashes($this->sitedata['site_title']));
        define('SITE_EMAIL', stripslashes($this->sitedata['site_email']));
    }
    /**
     *
     */
    public function AddLink()
    {
        echo $this->pagefilename . '?action=add';
    }
    /**
     * @return mysqli
     */
    private function DBConnection()
    {
        @include('../config.php');
        $con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
        if (mysqli_connect_errno()) {
            echo 'Failed to connect to MySQL: ' . mysqli_connect_error();
            exit;
        } else {
            return $con;
        }
    }
    /**
     * @param string $date1
     * @param string $date2
     *
     * @return array
     */
    public function DateDifference($date1, $date2)
    {
        $difference = abs(strtotime($date1) - strtotime($date2));
        $difference_array['hours'] = ceil($difference / (60 * 60));
        $difference_array['days'] = floor($difference_array['hours'] / 24);
        $difference_array['extra_hours'] = abs($difference_array['hours'] % 24);
        return $difference_array;
    }
    /**
     * @param array $array
     */
    public function DeleteFile(array $array)
    {
        foreach ($array['files'] as $key => $value) {
            @unlink($array['uploadpath'] . $value);
        }
    }
    /**
     * @param string $id
     */
    public function DeleteLink($id)
    {
        echo $this->pagefilename . '?action=delete&id=' . $id;
    }
    /**
     * @param string $tablename
     * @param string $where
     * @param int    $limit
     *
     * @return int
     */
    public function DeleteRecord($tablename, $where, $limit = 0)
    {
        /*
            REQUIREMENT :
                $tablename : Table Name where data will be inserted
                $where : 	Where condition in string : id = 1, id =1 and status=1

            RETURN : number of deleted records
        */
        if (TABLE_PREFIX != '') {
            $tablename = TABLE_PREFIX . $tablename;
        }
        $query_string = 'delete from ' . $tablename . ' ';
        if ($where != '') {
            $query_string .= ' where ' . $where;
        }
        if ($limit > 0) {
            $query_string .= ' limit ' . $limit;
        }
        /* TO CHECK QUERY REMOVE ENABLE BELOW CODE */
        //echo $query_string;exit;
        $con = $this->DBConnection();
        mysqli_query($con, $query_string);
        $totaldeleted = mysqli_affected_rows($con);
        mysqli_close($con);
        return $totaldeleted;
    }
    /**
     * @param string $id
     */
    public function EditLink($id)
    {
        echo $this->pagefilename . '?action=edit&id=' . $id;
    }
    /**
     * @param string $query_string
     *
     * @return array
     */
    public function GetCustom($query_string)
    {
        /*
            REQUIREMENT :
                    $query = query string as per your requirements
        */
        $con = $this->DBConnection();
        $query = mysqli_query($con, $query_string);
        if (@mysqli_num_rows($query) > 0) {
            while ($data = mysqli_fetch_assoc($query)) {
                $record_array[] = $data;
            }
            mysqli_free_result($query);
        }
        mysqli_close($con);
        return $record_array;
    }
    /**
     * @param string $string
     *
     * @return string
     */
    protected function GetPassword($string)
    {
        if (!empty($string)) {
            $string = SECRET_KEY . $string;        //SECRET_KEY IS DEFINED IN THE config.php FILE.
            $plain_text = base64_decode($string);
            return str_replace(SECRET_KEY, '', $plain_text);
            //return sha1($string);
            //return hash("sha256", $string);
            //return hash("sha512", $string);
        }
        return $string;
    }
    /**
     * @param int $length
     * @param int $type
     *
     * @return string
     */
    public function GetRandomString($length, $type = 0)
    {
        $key = '';
        if ($type == 1)        //NUMERIC ONLY
        {
            $keys = range(0, 9);
        } else {
            if ($type == 2)    //ALPHA ONLY
            {
                $keys = range('a', 'z');
            } else {
                $keys = array_merge(range(0, 9), range('a', 'z'));
            }
        }
        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }
        return $key;
    }
    /**
     * @param string $tablename
     * @param array  $array
     *
     * @return array
     */
    public function GetRecord($tablename, array $array)
    {
        /*
            REQUIREMENT :
                $tablename : Table Name where data will be inserted
                $array :  array
                        fields : * or field_1, field_2, field_n #BY DEFAULT *
                        where : where condition as per your requirement
                        orderby	: order by parameter : #BY DEFAULT PRIMARY KEY
                        ordertype	: order type parameter : #BY DEFAULT PRIMARY KEY desc
                        limit : limit of the record, 10 or 20 or n...
                        startfrom : record starts from
                        groupby : group by
            RETURN : RECORD ARRAY
        */
        if (TABLE_PREFIX != '') {
            $tablename = TABLE_PREFIX . $tablename;
        }
        $record = [];
        if (!isset($array['fields']) || $array['fields'] == '') {
            $array['fields'] = '*';
        }
        $query_string = 'select ' . $array['fields'] . ' from ' . $tablename . ' where 1=1 ';
        if (@$array['where'] != '') {
            $query_string .= ' and ' . $array['where'] . ' ';
        }
        //setting group by
        if (@$array['groupby'] != '') {
            $query_string .= ' group by ' . $array['groupby'];
        }
        //seeting order by
        if (@$array['orderby'] == '') {
            $array['orderby'] = 1;
        }
        //setting order type
        if (@$array['ordertype'] == '') {
            $array['ordertype'] = 'desc';
        }
        $query_string .= ' order by ' . $array['orderby'] . ' ' . $array['ordertype'];
        //setting record start limit
        if (@$array['startfrom'] == '') {
            $array['startfrom'] = 0;
        }
        //setting record limit
        if (@$array['limit'] > 0 && is_numeric(@$array['limit'])) {
            $query_string .= ' limit ' . $array['startfrom'] . ', ' . $array['limit'];
        }
        /* TO CHECK QUERY REMOVE ENABLE BELOW CODE */
        //echo $query_string;exit;
        $con = $this->DBConnection();
        $query = mysqli_query($con, $query_string);
        if (@mysqli_num_rows($query) > 0) {
            while ($data = mysqli_fetch_assoc($query)) {
                $record[] = $data;
            }
            mysqli_free_result($query);
        }
        mysqli_close($con);
        return $record;
    }
    /**
     * @param string $tablename
     * @param array  $array
     *
     * @return array|null
     */
    public function GetSingleRecord($tablename, array $array)
    {
        /*
            REQUIREMENT :
                $tablename : Table Name where data will be inserted
                $array :  array
                        fields : * or field_1, field_2, field_n #BY DEFAULT *
                        where : where condition as per your requirement
            RETURN : RECORD ARRAY
        */
        if (TABLE_PREFIX != '') {
            $tablename = TABLE_PREFIX . $tablename;
        }
        $record = [];
        if (!isset($array['fields']) || $array['fields'] == '') {
            $array['fields'] = '*';
        }
        $query_string = 'select ' . $array['fields'] . ' from ' . $tablename . ' where 1=1 ';
        if (@$array['where'] != '') {
            $query_string .= ' and ' . $array['where'] . ' ';
        }
        //setting group by
        if (@$array['groupby'] != '') {
            $query_string .= ' group by ' . $array['groupby'];
        }
        //seeting order by
        if (@$array['orderby'] == '') {
            $array['orderby'] = 1;
        }
        //setting order type
        if (@$array['ordertype'] == '') {
            $array['ordertype'] = 'desc';
        }
        $query_string .= ' order by ' . $array['orderby'] . ' ' . $array['ordertype'];
        //setting record start limit
        if (@$array['startfrom'] == '') {
            $array['startfrom'] = 0;
        }
        /* TO CHECK QUERY REMOVE ENABLE BELOW CODE */
        //echo $query_string;exit;
        $con = $this->DBConnection();
        $query = mysqli_query($con, $query_string);
        if (@mysqli_num_rows($query) > 0) {
            $record = mysqli_fetch_assoc($query);
            mysqli_free_result($query);
        }
        mysqli_close($con);
        return $record;
    }
    /**
     * @param string $tablename
     * @param array  $fieldarray
     * @param array  $valuearray
     *
     * @return int
     */
    public function InsertMultipleRecord($tablename, array $fieldarray, array $valuearray)
    {
        if (TABLE_PREFIX != '') {
            $tablename = TABLE_PREFIX . $tablename;
        }
        $query_string = 'insert into ' . $tablename . ' (';
        foreach ($fieldarray as $key => $value) {
            $query_string .= '`' . $value . '` ,';
        }
        $query_string = trim($query_string, ' ,');
        $query_string .= ' ) values ';
        $con = $this->DBConnection();
        foreach ($valuearray as $key => $value) {
            $query_string .= ' ( ';
            foreach ($value as $k => $v) {
                $query_string .= "'" . mysqli_real_escape_string($con, $v) . "' ,";
            }
            $query_string = trim($query_string, ' ,');
            $query_string .= ' ) ,';
        }
        $query_string = trim($query_string, ' ,');
        /* TO CHECK QUERY REMOVE ENABLE BELOW CODE */
        //echo $query_string;exit;
        mysqli_query($con, $query_string);
        $totalnewrecord = mysqli_affected_rows($con);
        mysqli_close($con);
        return $totalnewrecord;
    }
    /**
     * @param string $tablename
     * @param array  $values
     *
     * @return int|string
     */
    public function InsertRecord($tablename, array $values)
    {
        /*
            REQUIREMENT :
                $tablename : Table Name where data will be inserted
                $values :  array
                            field_1 => value_1,field_2 => value_2,field_3 => value_3,field_n => value_n

                            fields name and values which will be added for the record

            RETURN : LAST INSERTED IDs
        */
        $last_inserted_id = 0;
        if (TABLE_PREFIX != '') {
            $tablename = TABLE_PREFIX . $tablename;
        }
        if (!empty($values)) {
            $con = $this->DBConnection();
            $query_string = 'insert into ' . $tablename . ' set ';
            foreach ($values as $key => $value) {
                $query_string .= $key . " = '" . addslashes(mysqli_real_escape_string($con, $value)) . "' , ";
            }
            $query_string = trim($query_string, ' , ');
            /* TO CHECK QUERY REMOVE ENABLE BELOW CODE */
            //echo $query_string;exit;
            mysqli_query($con, $query_string);
            $last_inserted_id = mysqli_insert_id($con);
            mysqli_close($con);
        }
        return $last_inserted_id;
    }
    /**
     * @param string $string
     *
     * @return string
     */
    protected function MakePassword($string)
    {
        if (!empty($string)) {
            $string = SECRET_KEY . $string;        //SECRET_KEY IS DEFINED IN THE config.php FILE.
            return base64_encode($string);
            //return sha1($string);
            //return hash("sha256", $string);
            //return hash("sha512", $string);
        }
        return $string;
    }
    /**
     * @param string $tablename
     * @param array  $array
     */
    public function PagiNation($tablename, $array = [])
    {
        /*
            REQUIREMENT :
            $query : By defualt it will be generated from getrecord functions. You can also provide with custom query.
            RETURN : display the pagination number in list
        */
        if (@$array['query'] != '') {
            $query = $array['query'];
        } else {
            if (TABLE_PREFIX != '') {
                $tablename = TABLE_PREFIX . $tablename;
            }
            $query = 'select count(1) as total from ' . $tablename . ' where 1=1 ';
            if (@$array['where'] != '') {
                $query .= ' and ' . $array['where'] . ' ';
            }
            if (@$array['groupby'] != '') {
                $query .= ' group by ' . $array['groupby'];
            }
        }
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        $data = $this->GetCustom($query);
        $totalrecord_data = $data[0];
        if ($totalrecord_data['total'] > 0) {
            $lastpage = ceil($totalrecord_data['total'] / $_SESSION['pagerecords_limit']);
            if ($lastpage > 1) {
                echo '<ul class="pagination">';
                if (@$_GET['page'] > 1) {
                    echo '<li><a href="' . $_SERVER['PHP_SELF'] . '?' . $this->PagingQueryString(1) . '">&laquo;</a></li>';
                }
                $maxpage_display = 5;
                $startpage = 1;
                if ($lastpage > $maxpage_display) {
                    $endpage = $maxpage_display;
                } else {
                    $endpage = $lastpage;
                }
                if (@$_GET['page'] < $maxpage_display) {
                    $startpage = 1;
                } else {
                    $startpage = abs($_GET['page'] - 2);
                    if (($_GET['page'] + 2) < $lastpage) {
                        $endpage = abs($_GET['page'] + 2);
                    } else {
                        $endpage = $lastpage;
                    }
                }
                if ($startpage > 4) {
                    echo '<li><a href="' . $_SERVER['PHP_SELF'] . '?' . $this->PagingQueryString($startpage - 2) . '">&larr; Prev</a></li>';
                }
                for ($a = $startpage; $a <= $endpage; $a++) {
                    if (@$_GET['page'] == $a) {
                        echo '<li class="active"><a href="' . $_SERVER['PHP_SELF'] . '?' . $this->PagingQueryString($a) . '"  style="background:#ddd !important;">' . $a . '</a></li>';
                    } else {
                        echo '<li><a href="' . $_SERVER['PHP_SELF'] . '?' . $this->PagingQueryString($a) . '">' . $a . '</a></li>';
                    }
                }
                if ($endpage < ($lastpage - 3)) {
                    echo '<li><a href="' . $_SERVER['PHP_SELF'] . '?' . $this->PagingQueryString($endpage - 2) . '">Next &rarr;</a></li>';
                }
                if (@$_GET['page'] < $lastpage) {
                    echo '<li><a href="' . $_SERVER['PHP_SELF'] . '?' . $this->PagingQueryString($lastpage) . '">&raquo;</a></li>';
                }
                echo '</ul>';
            }
        }
        echo '</div>';
        echo '<div class="col-md-4">';
        if ($totalrecord_data['total'] > 20) {
            echo '<ul class="pagination">';
            echo '<li><a>Records/Page : </a></li>';
            $record_array = [20, 50, 100, 200];
            foreach ($record_array as $key => $value) {
                if ($_SESSION['pagerecords_limit'] == $value) {
                    $activerecord = 'style="background:#ddd !important;"';
                } else {
                    $activerecord = '';
                }
                echo '<li><a href="' . $_SERVER['PHP_SELF'] . '?record=' . $value . '" ' . $activerecord . '>' . $value . '</a></li>';
            }
            echo '</ul>';
        }
        echo '</div>';
        echo '</div>';
    }
    /**
     * @param int $pagevalue
     *
     * @return string
     */
    private function PagingQueryString($pagevalue = 1)
    {
        parse_str($_SERVER['QUERY_STRING'], $getarray);
        @$getarray['page'] = $pagevalue;
        return http_build_query($getarray);
    }
    /**
     * @param string $url
     */
    public function RedirectPage($url)
    {
        if ($url != '') {
            echo '<script type="text/javascript">window.location="' . $url . '";</script>';
            exit;
        }
    }
    /**
     * @param string $mailto
     * @param string $subject
     * @param string $message
     * @param string $attachments
     */
    public function SendMail($mailto, $subject, $message, $attachments = '')
    {
        // To send HTML mail, the Content-type header must be set
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        // Additional headers
        //$headers .= 'To: User1 <bharatparmar@example.com>, User2 <user2@example.com>' . "\r\n";
        $headers .= 'From: ' . SITE_TITLE . ' <' . SITE_EMAIL . '>' . "\r\n";
        //$headers .= 'Cc: user3@example.com' . "\r\n";
        //$headers .= 'Bcc: user4@example.com' . "\r\n";
        @mail($mailto, $subject, $message, $headers);
    }
    /**
     * @param string $id
     * @param string $currentstatus
     */
    public function StatusChangeLink($id, $currentstatus)
    {
        if ($currentstatus == '0') {
            echo '<a href="' . $this->pagefilename . '?action=status&status=1&id=' . $id . '"><span  class="label label-danger">Inactive</span></a>';
        } else {
            echo '<a href="' . $this->pagefilename . '?action=status&status=0&id=' . $id . '"><span  class="label label-success">Active</span></a>';
        }
    }
    /**
     * @param string $id
     * @param int    $status
     */
    public function StatusLink($id, $status = 0)
    {
        echo $this->pagefilename . '?action=status&status=' . $status . '&id=' . $id;
    }
    /**
     * @param string $tablename
     * @param array  $values
     * @param string $where
     *
     * @return int
     */
    public function UpdateRecord($tablename, array $values, $where = '')
    {
        /*
            REQUIREMENT :
                $tablename : Table Name where data will be inserted
                $values :  	array
                            field_1 => value_1,field_2 => value_2,field_3 => value_3,field_n => value_n

                            field names and values which will be updated.
                $where : 	Where condition in string : id = 1, id =1 and status=1

            RETURN : number of updated records
        */
        if (TABLE_PREFIX != '') {
            $tablename = TABLE_PREFIX . $tablename;
        }
        if (!empty($values)) {
            $con = $this->DBConnection();
            $query_string = 'update ' . $tablename . ' set ';
            foreach ($values as $key => $value) {
                $query_string .= $key . " = '" . addslashes(mysqli_real_escape_string($con, $value)) . "' , ";
            }
            $query_string = trim($query_string, ' , ');
            if ($where != '') {
                $query_string .= ' where ' . $where;
            }
            /* TO CHECK QUERY REMOVE ENABLE BELOW CODE */
            //echo $query_string;exit;
            mysqli_query($con, $query_string);
            $totalupdated = mysqli_affected_rows($con);
            mysqli_close($con);
        }
        //return mysql_affected_rows();
        return $totalupdated;
    }
    /**
     * @param array $files
     * @param array $array
     *
     * @return array
     */
    public function UploadFile(array $files, array $array)
    {
        $uploaded_files = [];
        if (isset($files) && $files['name'] != '') {
            //CHANGING PERMISSION OF THE DIRECTORY
            @chmod($array['uploadpath'], 0755);
            if ($array['limit'] == 0 || $array['limit'] > @count($files['name'])) {
                $array['limit'] = @count($files['name']);
            }
            for ($a = 0; $a < $array['limit']; $a++) {
                if (@$array['maxsize'] <= 0) {
                    $array['maxsize'] = 5000;
                }
                $allowedfiletypes = $array['filetype'];
                $max_size = $array['maxsize'] * 1000;    //in KB
                $filename = '';
                if ($array['limit'] > 1) {
                    $currentfile_extension = end(@explode('.', $files['name'][$a]));
                    if (in_array(strtolower($currentfile_extension), $allowedfiletypes)) {
                        $filename = date('YmdHis') . rand(1000, 9999) . '.' . $currentfile_extension;
                        if ($files['size'][$a] < $max_size) {
                            if (@move_uploaded_file($files['tmp_name'][$a], $array['uploadpath'] . $filename)) {
                                $uploaded_files[] = $filename;
                                //CHANGIN FILE PERMISSION
                                @chmod($array['uploadpath'] . $filename, 0755);
                            }
                        }
                    }
                } else {
                    $currentfile_extension = end(@explode('.', $files['name']));
                    if (in_array(strtolower($currentfile_extension), $allowedfiletypes)) {
                        $filename = date('YmdHis') . rand(1000, 9999) . '.' . $currentfile_extension;
                        if ($files['size'][$a] < $max_size) {
                            if (@move_uploaded_file($files['tmp_name'], $array['uploadpath'] . $filename)) {
                                $uploaded_files[] = $filename;
                                //CHANGIN FILE PERMISSION
                                @chmod($array['uploadpath'] . $filename, 0755);
                            }
                        }
                    }
                }
            }
        }
        return $uploaded_files;
    }
    /**
     * @param string $id
     */
    public function ViewLink($id)
    {
        echo $this->pagefilename . '?action=view&id=' . $id;
    }
    /**
     * @param string $value
     * @param string $function
     *
     * @return bool
     */
    public function validate($value, $function = 'require')
    {
        $response = false;
        if ($function == 'require' && trim($value) != '') {
            $response = true;
        }
        if (trim($value) != '' && $function == 'numeric' && is_numeric($value)) {
            $response = true;
        }
        if (trim($value) != '' && $function == 'alpha' && preg_match("/^[a-zA-Z ]*$/", $value)) {
            $response = true;
        }
        if (trim($value) != '' && $function == 'alphanumeric' && preg_match("/^[a-zA-Z0-9 ]*$/", $value)) {
            $response = true;
        }
        if (trim($value) != '' && $function == 'email' && filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $response = true;
        } else {
            if (trim($value) != '' && preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",
                    $value)
            ) {
                $response = true;
            }
        }
        return $response;
    }
}
