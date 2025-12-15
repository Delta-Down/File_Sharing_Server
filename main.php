<!doctype html>

<!-- RENAME FILE LATER -->


<?php
//ini_set('display_errors', 1);
$uploadFile = fopen("/var/www/html/uploadtime.txt", "a+");
$readFile = file("/var/www/html/uploadtime.txt", FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
fclose($uploadFile);

foreach($readFile as &$line){
	$line = explode(",", $line);

	$line["name"] = $line[0]; unset($line[0]);
	$line["id"] = $line[1]; unset($line[1]);
	$line["time"] = $line[2]; unset($line[2]);

	$name = array_column($readFile, "name");
	$id = array_column($readFile, "id");
	$time = array_column($readFile, "time");
}


$vidresFile = fopen("vidres.txt", "a+");
$vidresRead = file("vidres.txt", FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
fclose($vidresFile);

foreach($vidresRead as &$line){
	$line = explode(",", $line);

	$line["name"] = $line[0]; unset($line[0]);
	$line["res"] = $line[1]; unset($line[1]);

	$resname = array_column($vidresRead, "name");
}
?>


<html>
<head>
	<title>._.</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href=".layout.css">
</head>
<body>
	<?php
	$itemnum = count(scandir("."));
	echo "<h1>the circuits are on the fritz again!<br>".$itemnum." files and directories</h1>"
	 ?>
	<table>
		<thead>
		</thead>
		<tbody>
			<tr class='dirrow'>
				<td><a href='..'>Go Back</a></td>
			<?php
			$fnum = 0;
			$dirs = array();
			$files = preg_grep('/^([^.])/', scandir("."));
			foreach($files as $file){ //List directories first
				if(is_dir($file) == true){
					array_unshift($dirs, $file);
					echo "<td><a href='./".$file."'>".$file."</td>";
				}
			}
			?>
			</tr>
			<?php
			$itemnum = 1;
			foreach($files as $file){ //Start creating rows
				if($file != "." && $file != ".." && is_dir($file) != true){
					if(strpos($file, " ") != false){
						$renamed = str_replace(" ", "_", $file);
						rename($file, $renamed);
						$file = $renamed;
					}

					$fnum++;
					$flink = "./$file";
					$filesize = filesize("./$file");
					//$filemtime = date("m/d/y h:i:s A", filemtime($file));

					$mimetype = explode("/", mime_content_type($file));
					$tn = explode(".", $file); $tn = $tn[0]; $tn = str_replace(" ", "_", $tn); $tn = str_replace("(", "", $tn);$tn = str_replace(")", "", $tn);
					$tnpath = ".thumbnails/"."$tn"."-tn.jpg";

					if($mimetype[0] == "image"){
						$csfile = "'$file'";
						$dim = getimagesize($file);
						$special = "$dim[0] x $dim[1]";
						unset($dim);
						$cmd = "ffmpeg -i ".$csfile." -vf scale=150:150 -q:v 6 ".$tnpath;
						if(!file_exists($tnpath)){
							exec($cmd, $output, $retval);
							$conmessage = "STARTING TO CONVERT";
						}else {
							$conmessage = $mimetype[0];
						}
					}else {
						$conmessage = $mimetype[0];
					}

					if($mimetype[0] == "video"){
						$csfile = "'$file'";
						$here = explode("/", getcwd(), 5);
						$flink = "/files/.theater/?content=".$file."&referer=".$here[4];
						$cmd1 = "ffprobe -v error -select_streams v:0 -show_entries stream=width,height -of default=nw=1:nk=1 ".$csfile;

						if(!in_array("$file", $resname)){
							exec($cmd1, $dim, $retval);
							file_put_contents("vidres.txt", "$file,$dim[0] x $dim[1]\n", FILE_APPEND);
						} else{
							foreach($vidresRead as $line){
								if($line["name"] == $file){
									$special = $line["res"];
									//For some reason,  an & is placed before the last array and doesn't display the resolution for the most recently uploaded video ¯\_(ツ)_/¯
								}
							}
						}
						unset($dim);

						$cmd = "ffmpeg -i ".$csfile." -ss 00:00:01 -frames:v 1 -s 150x150 ".$tnpath;
						if(!file_exists($tnpath)){
							exec($cmd, $output, $retval);
							$conmessage = "STARTING TO CONVERT";
						}else {
							$conmessage = $mimetype[0];
						}
					}else {
						$conmessage = $mimetype[0];
					}

					if($mimetype[0] != "image" && $mimetype[0] != "video"){
						$tnpath = "/.icons/file.png";
					}

					$imgsize = getimagesize($tnpath);
					$width = $imgsize[0];
					$height = $imgsize[1];

					$ctime = date("m/d/y h:i:s A", time());
					if(!in_array("$file", $name)){
						$fid = bin2hex(random_bytes(10));
						if(!in_array("$fid", $id)){
							file_put_contents("/var/www/html/uploadtime.txt", "$file,$fid,$ctime\n", FILE_APPEND);
							echo $fid;
							//var_dump($readFile);
						} else{
							echo "fail :( ";
						}
					} else{
						foreach($readFile as $line){
							if($line["name"] == $file){
								//echo $line["time"];
								$uploadTime = $line["time"];
							}
						}
					}

					if($filesize < 1024 && $filesize >= 1){ //Make file size readable
						$convfilesize = "$filesize B";
					}
					if($filesize >= 1024 && $filesize <= pow(1024, 2)){
						$convfilesize = ($filesize / 1024);
						if($convfilesize >= 100){
							$convfilesize = round($convfilesize);
						}else {
							$convfilesize = round($convfilesize, 2);
						}
						$convfilesize = "$convfilesize KB";
					}
					if($filesize >= pow(1024, 2) && $filesize <= pow(1024, 3)){
						$convfilesize = ($filesize / pow(1024, 2));
						if($convfilesize >= 100){
							$convfilesize = round($convfilesize);
						}else {
							$convfilesize = round($convfilesize, 2);
						}
						$convfilesize = "$convfilesize MB";
					}
					if($filesize >= pow(1024, 3) && $filesize <= pow(1024, 4)){
						$convfilesize = ($filesize / pow(1024, 3));
						if($convfilesize >= 100){
							$convfilesize = round($convfilesize);
						}else {
							$convfilesize = round($convfilesize, 2);
						}
						$convfilesize = "$convfilesize GB";
					}
					if($filesize >= pow(1024, 4) && $filesize <= pow(1024, 5)){
						$convfilesize = ($filesize / pow(1024, 4));
						if($convfilesize >= 100){
							$convfilesize = round($convfilesize);
						}else {
							$convfilesize = round($convfilesize, 2);
						}
						$convfilesize = "$convfilesize TB";
					}
					echo "<tr class='filerow'><td class='fimage'><a href='".$flink."'><img src='".$tnpath."' alt='".$tn."' width='".$width."' height='".$height."'></td><td class='ffile'><a href='".$flink."'>".$file."</td><td class='ftype'>".$conmessage."</td><td class='fsize'>".$convfilesize."</td><td class='ftime'>".$special."</td><td class='utime'>".$uploadTime."</td></tr>";
					unset($special);
				}
			}
			echo $ctime;
			?>
		</tbody>
	</table>
	<table>
</body>

