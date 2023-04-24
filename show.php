<?php 
include 'bd.php';
$select = "SELECT message.id,message.time,message.text,message.post_id, user.id as user_id, user.user_name as name, user.user_nick as nick,post.time as post_time,post.channel_id as channel_id, post.text as post_text,channel.user as channel_user FROM `message` JOIN user ON user.id=`user_id` JOIN post ON post.id=`post_id` JOIN channel ON post.channel_id=channel.id GROUP BY message.id limit 200";
$data_user = mysqli_query($bd,$select);

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
</head>
<body>
<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
</style>
</head>
<body>

<h2>Messages</h2>

<table>
  <tr>
    <th>id</th>
    <th>Message Text</th>
    <th>User Id</th>
    <th>User Nick</th>
    <th>User Name</th>
    <th>Message Time</th>
    <th>Post Channel</th>
    <th>Post Link</th>
    <th>Post Text</th>
    <th>Post Time</th>
  </tr>
<?php while ($cat = mysqli_fetch_assoc($data_user)) { ?>
  <tr>
    <th><?=$cat['id']?></th>
    <th><?=$cat['text']?></th>
    <th><?=$cat['user_id']?></th>
    <th><?=$cat['name']?></th>
    <th><?=$cat['nick']?></th>
    <th><?=date('Y-m-d\TH:i:s\Z',$cat['time'])?></th>
    <th><?=$cat['channel_user']?></th>
    <th><a href="https://t.me/<?=str_replace('@', '', $cat['channel_user'])?>/<?=$cat['post_id']?>"  target="_blank">post link</a></th>
    <th><?=$cat['post_text']?></th>
    <th><?=date('Y-m-d\TH:i:s\Z',$cat['post_time'])?></th>
  </tr>
<?php } ?>
</table>

</body>
</html>