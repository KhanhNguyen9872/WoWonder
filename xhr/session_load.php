<?php 
if ($f == 'session_load') {
    $html = "";
    if (!empty($_POST['ids']) && is_array($_POST['ids'])) {
        $ids = array();
        foreach ($_POST['ids'] as $key => $value) {
            if (!empty($value) && is_numeric($value)) {
                $ids[] = Wo_Secure($value);
            }
        }
        $get_sessions = Wo_GetAllSessionsFromUserID($wo['user']['user_id'],10,$ids);
        if (count($get_sessions) > 0) {
            foreach ($get_sessions as $wo['key'] => $wo['session']) {
                $html .= Wo_LoadPage('setting/includes/sessions');
            }
        }
    }
    $data['status'] = 200;
    $data['html'] = $html;
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}