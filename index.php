<?php
if (!file_exists('madeline.php')) {
    copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
}
include 'madeline.php';
include 'bd.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
$MadelineProto = new \danog\MadelineProto\API('session.madeline');
$MadelineProto->start();

$me = $MadelineProto->getSelf();

$MadelineProto->logger($me);

if (!$me['bot']) {
	echo "<pre>";
	$list = "SELECT * FROM channel";
	$data = mysqli_query($bd,$list);
	$message_list = array();
	while ($cat = mysqli_fetch_assoc($data)) {
		$channel = $cat['user'];
		print_r($channel);
		try {
			$messages = $MadelineProto->messages->getHistory(['peer' => $channel, 'offset_id' => 0, 'offset_date' => 0, 'add_offset' => 0, 'limit' => 20, 'max_id' => 1000000, 'min_id' => 0, 'hash' => 0]);
		} catch (Exception $e) {
			print_r($e->getMessage());
		}
		foreach ($messages['messages'] as $message) {
			$messed_arr = array();
			$messed_arr['id'] = $message['id'];
			$messed_arr['date'] = $message['date'];
			$messed_arr['message'] = $message['message'];
			$messed_arr['channel_id'] = $cat['id'];
			$messed_arr['channel'] = $channel;
			$message_list[] = $messed_arr;
		}
	}
	foreach ($message_list as $message) {
		if ($message['message']!='') {
			$channel = $message['channel'];
			$post = $message;
			$add = addPost($message);
			try {
				$messages_DiscussionMessage = $MadelineProto->messages->getReplies(['peer' => $channel, 'msg_id' => $message['id'],'limit'=>100000, 'offset_date' => time()]);					
			} catch (Exception $e) {
				$messages_DiscussionMessage='';
			}
			if ($messages_DiscussionMessage!='') {
				$users_list = array();
				foreach ($messages_DiscussionMessage['users'] as $users ) {
					$user = array();
					$user['id'] = $users['id'];
					$user['first_name'] = "";
					$user['username'] = "";
					if (isset( $users['username'])) {
						$user['username'] = $users['username'];
					}
					if (isset( $users['first_name'])) {
						$user['first_name'] = remove_emoji($users['first_name']);
					}
					$users_list[$users['id']]  = $user;

					$qwery = "SELECT * FROM user WHERE id=".$user['id'];
					$data_user = mysqli_query($bd,$qwery);
					if (mysqli_num_rows($data_user)) {
					}else{
						try {
								$query = "INSERT INTO `user`(`id`, `user_name`, `user_nick`) VALUES (".$user['id'].",'".$user['first_name']."','".$user['username']."')";
								$data = mysqli_query($bd,$query);
							} catch (Exception $e) {
								print_r($e->getMessage());
							}
					};
				}
				foreach ($messages_DiscussionMessage['messages'] as $user_message) {
					// $users_list[$user_message['from_id']['user_id']]['message'] = $user_message['message'];
					$mess_string = remove_emoji($user_message['message']);
					$qwery = "SELECT * FROM message WHERE id=".$user_message['id']." and post_id=".$post['id']."";
					$data_user = mysqli_query($bd,$qwery);
					if (mysqli_num_rows($data_user)) {
					}else{
						if (isset($user_message["from_id"]["user_id"])) {
							try {
								$query = "INSERT INTO `message`(`id`, `time`, `text`, `post_id`, `user_id`) VALUES ('".$user_message["id"]."','".$user_message["date"]."','".$mess_string."',".$post["id"].",".$user_message["from_id"]["user_id"].")";
								$data = mysqli_query($bd,$query);
							} catch (Exception $e) {
								print_r($e->getMessage());
							}
						}
					};
				}
			}else{
			}
		}
	}
}
function remove_emoji($string)
{
    // Match Enclosed Alphanumeric Supplement
    $regex_alphanumeric = '/[\x{1F100}-\x{1F1FF}]/u';
    $clear_string = preg_replace($regex_alphanumeric, '', $string);

    // Match Miscellaneous Symbols and Pictographs
    $regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $clear_string = preg_replace($regex_symbols, '', $clear_string);

    // Match Emoticons
    $regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
    $clear_string = preg_replace($regex_emoticons, '', $clear_string);

    // Match Transport And Map Symbols
    $regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
    $clear_string = preg_replace($regex_transport, '', $clear_string);
    
    // Match Supplemental Symbols and Pictographs
    $regex_supplemental = '/[\x{1F900}-\x{1F9FF}]/u';
    $clear_string = preg_replace($regex_supplemental, '', $clear_string);

    // Match Miscellaneous Symbols
    $regex_misc = '/[\x{2600}-\x{26FF}]/u';
    $clear_string = preg_replace($regex_misc, '', $clear_string);

    // Match Dingbats
    $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
    $clear_string = preg_replace($regex_dingbats, '', $clear_string);


	$clear_string = str_replace("'", '', $clear_string);
	$clear_string = str_replace('"', '', $clear_string);
	$clear_string = stripslashes($clear_string);
    return $clear_string;
}
function addPost($message){
	global $bd;
	$post['id'] = $message['id'];
	$post['date'] = $message['date'];
	$post['text'] = $message['message'];
	if (isset($message['message'])) {
		$post['text'] = remove_emoji($message['message']);
	}else{
		$post['text']='';
	}
	$post['channel_id'] = $message['channel_id'];
	$qwery = "SELECT * FROM post WHERE id=".$post['id']." and channel_id=".$post['channel_id']."";
	$data_search = mysqli_query($bd,$qwery);
	if (mysqli_num_rows($data_search)) {
	}else{
		try {
			$query = "INSERT INTO `post`(`id`, `time`, `text`, `channel_id`) VALUES ('".$post['id']."','".$post['date']."','".trim($post['text'])."',".$post['channel_id'].")";
			$data = mysqli_query($bd,$query);
		} catch (Exception $e) {
			print_r($e->getMessage());
		}
	};
	return true;
}