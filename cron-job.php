<?php
require_once('assets/init.php');

mysqli_query($sqlConnect, "UPDATE " . T_CONFIG . " SET `value` = '" . time() . "' WHERE `name` = 'cronjob_last_run'");
// ********** Pro Users **********
$users = $db->where('is_pro','1')->where('admin','0')->ArrayBuilder()->get(T_USERS);
foreach ($users as $key => $value) {
	$wo["user"] = Wo_UserData($value['user_id']);
	if ($wo["user"]["pro_type"] == 0) {
		$update      = Wo_UpdateUserData($wo["user"]["id"], array(
            "is_pro" => 0,
            'verified' => 0,
            'pro_' => 1
        ));
        $user_id     = $wo["user"]["id"];
        $mysql_query = mysqli_query($sqlConnect, "UPDATE " . T_PAGES . " SET `boosted` = '0' WHERE `user_id` = {$user_id}");
        $mysql_query = mysqli_query($sqlConnect, "UPDATE " . T_POSTS . " SET `boosted` = '0' WHERE `user_id` = {$user_id}");
        $mysql_query = mysqli_query($sqlConnect, "UPDATE " . T_POSTS . " SET `boosted` = '0' WHERE `page_id` IN (SELECT `page_id` FROM " . T_PAGES . " WHERE `user_id` = {$user_id})");
	}
	else{
		$notify = false;
	    $remove = false;

		if ($wo["pro_packages"][$wo["user"]["pro_type"]]['ex_time'] != 0) {
	        $end_time = $wo["user"]["pro_time"] + $wo["pro_packages"][$wo["user"]["pro_type"]]['ex_time'];
	        if ($end_time > time() && $end_time <= time() + 60 * 60 * 24 * 3) {
	            $notify = true;
	        } elseif ($end_time <= time()) {
	            $remove = true;
	        }
	    }

	    if ($notify == true) {
	        $start     = date_create(date("Y-m-d H:i:s", time()));
	        $end       = date_create(date("Y-m-d H:i:s", $end_time));
	        $diff      = date_diff($end, $start);
	        $left_time = "";
	        if (!empty($diff->d)) {
	            $left_time = $diff->d . " " . $wo["lang"]["day"];
	        } elseif (!empty($diff->h)) {
	            $left_time = $diff->h . " " . $wo["lang"]["hour"];
	        } elseif (!empty($diff->i)) {
	            $left_time = $diff->i . " " . $wo["lang"]["minute"];
	        }
	        $day       = date("d");
	        $month     = date("n");
	        $year      = date("Y");
	        $query_one = " SELECT COUNT(*) AS count FROM " . T_USERS . " WHERE `user_id` = " . $wo["user"]["id"] . " AND DAY(FROM_UNIXTIME(pro_remainder)) = '{$day}' AND MONTH(FROM_UNIXTIME(pro_remainder)) = '{$month}' AND YEAR(FROM_UNIXTIME(pro_remainder)) = '{$year}'";
	        $query     = mysqli_query($sqlConnect, $query_one);
	        if ($query) {
	            $fetched_data = mysqli_fetch_assoc($query);
	            if ($fetched_data["count"] < 1) {
	                $db->insert(T_NOTIFICATION, array(
	                    "recipient_id" => $wo["user"]["id"],
	                    "type" => "remaining",
	                    "text" => str_replace("{{time}}", $left_time, $wo["lang"]["remaining_text"]),
	                    "url" => "index.php?link1=home",
	                    "time" => time()
	                ));
	                $db->where('user_id',$wo["user"]["id"])->update(T_USERS,array('pro_remainder' => time()));
	            }
	        }
	    }
	    if ($remove == true) {
	    	if ($wo["user"]['wallet'] >= $wo["pro_packages"][$wo["user"]["pro_type"]]['price']) {
	    		$pro_type = $wo["user"]["pro_type"];
	    		$price = $wo["pro_packages"][$wo["user"]["pro_type"]]['price'];
	    		$update_array = array(
	                'is_pro' => 1,
	                'pro_time' => time(),
	                'pro_' => 1,
	                'pro_type' => $pro_type
	            );
	            if (in_array($pro_type, array_keys($wo['pro_packages'])) && $wo["pro_packages"][$pro_type]['verified_badge'] == 1) {
	                $update_array['verified'] = 1;
	            }
	            $mysqli             = Wo_UpdateUserData($wo['user']['user_id'], $update_array);
	            $notes = json_encode([
	                'pro_type' => $pro_type,
	                'method_type' => 'wallet'
	            ]);

	            $create_payment_log = mysqli_query($sqlConnect, "INSERT INTO " . T_PAYMENT_TRANSACTIONS . " (`userid`, `kind`, `amount`, `notes`) VALUES ({$wo['user']['user_id']}, 'PRO', {$price}, '{$notes}')");
	            $create_payment     = Wo_CreatePayment($pro_type);

	            $points = 0;
	            if ($wo['config']['point_level_system'] == 1) {
	                $points = $price * $wo['config']['dollar_to_point_cost'];
	            }
	            $wallet_amount  = ($wo["user"]['wallet'] - $price);
	            $points_amount  = ($wo['config']['point_allow_withdrawal'] == 0) ? ($wo["user"]['points'] - $points) : $wo["user"]['points'];
	            $query_one      = mysqli_query($sqlConnect, "UPDATE " . T_USERS . " SET `points` = '{$points_amount}', `wallet` = '{$wallet_amount}' WHERE `user_id` = {$wo['user']['user_id']} ");

	    	}
	    	else{
	    		$update      = Wo_UpdateUserData($wo["user"]["id"], array(
		            "is_pro" => 0,
		            'verified' => 0,
		            'pro_' => 1
		        ));
		        $user_id     = $wo["user"]["id"];
		        $mysql_query = mysqli_query($sqlConnect, "UPDATE " . T_PAGES . " SET `boosted` = '0' WHERE `user_id` = {$user_id}");
		        $mysql_query = mysqli_query($sqlConnect, "UPDATE " . T_POSTS . " SET `boosted` = '0' WHERE `user_id` = {$user_id}");
		        $mysql_query = mysqli_query($sqlConnect, "UPDATE " . T_POSTS . " SET `boosted` = '0' WHERE `page_id` IN (SELECT `page_id` FROM " . T_PAGES . " WHERE `user_id` = {$user_id})");
	    	}
		        
	    }
	}
}
// ********** Pro Users **********

// ********** Stories **********
$expired_stories = $db->where("expire", time(), "<")->get(T_USER_STORY);
if (!empty($expired_stories)) {
	foreach ($expired_stories as $key => $value) {
	    $db->where("story_id", $value->id)->delete(T_STORY_SEEN);
	}
	@mysqli_query($sqlConnect, "DELETE FROM " . T_USER_STORY_MEDIA . " WHERE `expire` < " . time());
	@mysqli_query($sqlConnect, "DELETE FROM " . T_USER_STORY . " WHERE `expire` < " . time());
}
	
// ********** Stories **********

// ********** Notifications **********
if ($wo["config"]["last_notification_delete_run"] <= time() - 60 * 60 * 24) {
    mysqli_multi_query($sqlConnect, " DELETE FROM " . T_NOTIFICATION . " WHERE `time` < " . (time() - 60 * 60 * 24 * 5) . " AND `seen` <> 0");
    mysqli_query($sqlConnect, "UPDATE " . T_CONFIG . " SET `value` = '" . time() . "' WHERE `name` = 'last_notification_delete_run'");
}
// ********** Notifications **********

// ********** Typing **********
Wo_GetOfflineTyping();
// ********** Typing **********


// ********** Live **********
if ($wo['config']['live_video'] == 1) {
	$user = $db->where('admin','1')->ArrayBuilder()->getOne(T_USERS);
	if (!empty($user)) {
		$wo['user'] = Wo_UserData($user['user_id']);
		$wo['loggedin'] = true;
		if ($wo['config']['live_video_save'] == 0) {
	        try {
	            $posts = $db->where('live_time','0','!=')->where('live_time',time() - 11,'<=')->get(T_POSTS);
	            foreach ($posts as $key => $post) {
	                if ($wo['config']['agora_live_video'] == 1 && !empty($wo['config']['agora_app_id']) && !empty($wo['config']['agora_customer_id']) && !empty($wo['config']['agora_customer_certificate']) && $wo['config']['live_video_save'] == 1) {
	                    StopCloudRecording(array('resourceId' => $post->agora_resource_id,
	                                             'sid' => $post->agora_sid,
	                                             'cname' => $post->stream_name,
	                                             'post_id' => $post->post_id,
	                                             'uid' => explode('_', $post->stream_name)[2]));
	                }
	                Wo_DeletePost(Wo_Secure($post->id),'shared');
	            }
	        } catch (Exception $e) {

	        }

	    }
	    else{
	        if ($wo['config']['agora_live_video'] == 1 && $wo['config']['amazone_s3_2'] != 1) {
	            try {
		            $posts = $db->where('live_time','0','!=')->where('live_time',time() - 11,'<=')->get(T_POSTS);
		            foreach ($posts as $key => $post) {
		                Wo_DeletePost(Wo_Secure($post->id),'shared');
		            }
		        } catch (Exception $e) {

		        }
	        }
	    }
	}
}
$posts = $db->where('stream_name','','<>')->where('postFile','')->get(T_POSTS);
if (!empty($posts)) {
    foreach ($posts as $key => $value) {
        if ((!empty($value->agora_resource_id) || !empty($value->agora_sid) || !empty($value->agora_token)) && empty($value->postFile)) {
            Wo_DeletePost($value->id,'shared');
        }
    }
}
// ********** Live **********
header("Content-type: application/json");
echo json_encode(["status" => 200, "message" => "success"]);
exit();