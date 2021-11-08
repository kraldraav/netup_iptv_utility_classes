<?php

class AppUsageStatController {

    protected $db;
    private $host = '127.0.0.1';
    private $login = '';
    private $password = '';
    private $dbname = 'STB_STATISTICS';
    private $dev_id = -1;

    public function __construct() {
        $this->db = mysqli_connect($this->host, $this->login, $this->password, $this->dbname);
    }

    public function getDB() {
        return $this->db;
    }

    public function setDB($DB) {
        $this->db = $DB;
    }

    public function getDeviceId($data) {
        $query = mysqli_prepare($this->db, "SELECT id from devices_table where device_name = ? AND mac = ? LIMIT 1;");
        $ss = 'ss';
        $query->bind_param($ss, $data['dev_name'], $data['mac']);
        $query->execute();
        $query->bind_result($id);
        $query->fetch();
        // $query->close();
        //var_dump($id);
        //if ($id = NULL) {
        if (!isset($id)) {
            $id = -1;
        }
        return $id;
    }

    public function insertDevice($data) {
        $this->dev_id = $this->getDeviceId($data);
        //var_dump($this->dev_id);
        if ($this->dev_id < 0) {
            $query = mysqli_prepare($this->db, "INSERT IGNORE INTO devices_table (device_name, mac) VALUES (?,?);");
            $ss = 'ss';
            if ($query == true) {
                $query->bind_param($ss, $data['dev_name'], $data['mac']);
                $query->execute();
            }
            //$query->close();
            $this->dev_id = $this->getDeviceId($data);
        }
        // var_dump($this->$dev_id)
        return $this->dev_id;
    }

    public function getPackage($data) {
        $query = mysqli_prepare($this->db, "SELECT id from app_usage_table where r_date = CURDATE() AND app_name = ? LIMIT 1;");
        $s = 's';
        $query->bind_param($s, $data['app_name']);
        $query->execute();
        $query->bind_result($id);
        $query->fetch();
        // $query->close();
        //var_dump($id);
        //if ($id = NULL) {
        if (!isset($id)) {
            $id = -1;
        }
        return $id;
    }

    public function insertAppStatistic($data) {
        $app_id = $this->getPackage($data);
        //var_dump($this->dev_id);
        if ($app_id < 0) {
            $query = mysqli_prepare($this->db, "INSERT IGNORE INTO app_usage_table (r_date, app_name,working_time, device_id) VALUES (CURDATE(),?,?,?);");
            $sii = 'sii';
            if ($query == true) {
                $query->bind_param($sii, $data['app_name'], $data['working_time'], $data['device_id']);
                $query->execute();
            }
            //$query->close();
        } else {
            $query = mysqli_prepare($this->db, "UPDATE app_usage_table SET working_time = ? WHERE id = ?");
            $sii = 'si';
            if ($query == true) {
                $query->bind_param($sii, $data['working_time'], $app_id);
                $query->execute();
            }
        }
        // var_dump($this->$dev_id)
    }

    public function getAppsByDate($data) {
        $query = mysqli_prepare($this->db, "SELECT * from app_usage_table where r_date = ? AND device_id = ?;");
        $ss = 'ss';
        if ($query == true) {
            $query->bind_param($ss, $data['current_date'], $data['device_id']);
            $query->execute();
        }
        $myArray = [];
        $result = $query->get_result();
        while ($row = $result->fetch_assoc()) {
            array_push($myArray, $row);
        }
        return json_encode($myArray);
    }
    
    public function getAppsUsageData() {
        $query = mysqli_prepare($this->db, "SELECT r_date, app_name, working_time, devices_table.device_name, devices_table.mac FROM app_usage_table LEFT JOIN devices_table ON app_usage_table.device_id = devices_table.id;");
        if ($query == true) {
            $query->execute();
        }
        $myArray = [];
        $result = $query->get_result();
        while ($row = $result->fetch_assoc()) {
            array_push($myArray, $row);
        }
        return $myArray;
    }
    
    public function getAppsUsageDataByDate($StartDate,$EndDate){
        $query = mysqli_prepare($this->db, "SELECT r_date, app_name, working_time, devices_table.device_name, devices_table.mac FROM app_usage_table LEFT JOIN devices_table ON app_usage_table.device_id = devices_table.id WHERE r_date >= ? AND r_date <= ?;");
        $ss = 'ss';
        if ($query == true) {
            $query->bind_param($ss, $StartDate, $EndDate);
            $query->execute();
        }
        $myArray = [];
        $result = $query->get_result();
        while ($row = $result->fetch_assoc()) {
            array_push($myArray, $row);
        }
        return $myArray;
    }
    
    public function  insertMasterPassword($data){
        $query = mysqli_prepare($this->db, "INSERT INTO master_passwords (device_id, date_time, package) VALUES (?,?,?);");
            $ss = 'iss';
            if ($query == true) {
                $query->bind_param($ss, $data['device_id'], $data['date_time'], $data['package']);
                $query->execute();
            }
    }

}
