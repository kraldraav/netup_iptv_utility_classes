<?php

include dirname(__FILE__) . "/tool/AppUsageStatController.php";

$Controller = new AppUsageStatController();

//file_put_contents('post.txt', json_encode($_POST));

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data["dev_name"]) && isset($data["mac"])) {
    $data_answer['dev_id'] = $Controller->insertDevice($data);
    echo json_encode($data_answer);
} else if (isset($data["app_name"]) && isset($data["working_time"]) && isset($data["device_id"])) {
    $Controller->insertAppStatistic($data);
    $answer["response"] = "ok";
    echo json_encode($answer);
} else if (isset($data["current_date"]) && isset($data["device_id"])) {
    echo $Controller->getAppsByDate($data);
} if (isset($data["device_id"]) && isset($data["date_time"]) && isset($data["package"])) {
    $Controller->insertMasterPassword($data);
    $answer["response"] = "ok";
    echo json_encode($answer);
}


