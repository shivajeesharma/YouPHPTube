<?php
$config = dirname(__FILE__) . '/../../../videos/configuration.php';
require_once $config;

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$cdnObj = AVideoPlugin::getDataObjectIfEnabled('CDN');

if (empty($cdnObj)) {
    return die('Plugin disabled');
}
$startFromIndex = intval(@$argv[1]);
$_1hour = 3600;
$_2hours = $_1hour * 2;
ob_end_flush();
set_time_limit($_2hours);
ini_set('max_execution_time', $_2hours);
$parts = explode('.', $cdnObj->storage_hostname);
$apiAccessKey = $cdnObj->storage_password;
$storageZoneName = $cdnObj->storage_username; // Replace with your storage zone name
$storageZoneRegion = trim(strtoupper($parts[0])); // Replace with your storage zone region code

echo ("CDNStorage::APIput line $apiAccessKey, $storageZoneName, $storageZoneRegion [startFromIndex=$startFromIndex]") . PHP_EOL;
$client = new \Bunny\Storage\Client($apiAccessKey, $storageZoneName, $storageZoneRegion);
echo ("CDNStorage::APIput line " . __LINE__) . PHP_EOL;

$sql = "SELECT * FROM  videos WHERE 1=1 ORDER BY id ";
$res = sqlDAL::readSql($sql, "", [], true);
echo ("CDNStorage::APIput line " . __LINE__) . PHP_EOL;
$fullData = sqlDAL::fetchAllAssoc($res);
echo ("CDNStorage::APIput line " . __LINE__) . PHP_EOL;
sqlDAL::close($res);
echo ("CDNStorage::APIput line " . __LINE__) . PHP_EOL;

$secondsInAMinute = 60;
$secondsInAnHour = 60 * $secondsInAMinute;
$secondsInADay = 24 * $secondsInAnHour;
$secondsInAWeek = 7 * $secondsInADay;
$secondsInAMonth = 30 * $secondsInADay; // Approximate, varies by month

$totalProcessedSize = 0; // Total size of files processed
$totalProcessedTime = 0; // Total time taken to process the files

$totalProcessedTime = 0; // Total time taken to process videos
$processedVideosCount = 0; // Number of videos processed

if ($res != false) {
    $total = count($fullData);
    echo ("CDNStorage::APIput found {$total} videos") . PHP_EOL;
    foreach ($fullData as $key => $row) {
        if ($key < $startFromIndex) {
            continue;
        }
        $videos_id = $row['id'];
        $info1 = "videos_id = $videos_id [{$total}, {$key}] ";
        $list = CDNStorage::getFilesListBoth($videos_id);
        $totalFiles = count($list);
        echo ("{$info1} CDNStorage::APIput found {$totalFiles} files for videos_id = $videos_id ") . PHP_EOL;
        $count = 0;
        $totalSizeRemaining = array_sum(array_map(function ($value) {
            return $value['isLocal'] ? filesize($value['local']['local_path']) : 0;
        }, $list));

        foreach ($list as $value) {
            $count++;
            $info2 = "{$info1}[{$totalFiles}, {$count}] ";
            if (empty($value['local'])) {
                continue;
            }
            $filesize = filesize($value['local']['local_path']);
            if ($value['isLocal'] && $filesize > 20) {
                if (empty($value) || empty($value['remote']) || $filesize != $value['remote']['remote_filesize']) {
                    $remote_file = CDNStorage::filenameToRemotePath($value['local']['local_path']);
                    $startTime = microtime(true);
                    echo PHP_EOL . ("$info2 CDNStorage::APIput {$value['local']['local_path']} {$remote_file} " . humanFileSize($filesize)) . PHP_EOL;
                    try {
                        $client->upload($value['local']['local_path'], $remote_file);
                    } catch (\Throwable $th) {
                        echo "$info2 CDNStorage::APIput Upload ERROR " . $th->getMessage() . PHP_EOL;
                    }
                    $endTime = microtime(true);
                    $timeTaken = $endTime - $startTime; // Time taken in seconds
                    $totalSizeRemaining -= $filesize; // Update remaining size
                    $timeTakenFormated = number_format($timeTaken, 1);
                    $speed = $filesize / $timeTaken; // Bytes per second
                    $etaForCurrentFile = $totalSizeRemaining / $speed; // ETA in seconds

                    $totalProcessedSize += $filesize; // Update the total processed size
                    $totalProcessedTime += $timeTaken; // Update the total processed time
                    $processedVideosCount++;
                    // Calculate the average time per video
                    $averageTimePerVideo = $totalProcessedTime / $processedVideosCount;

                    // Calculate the average speed so far (bytes per second)
                    $averageSpeed = $totalProcessedSize / $totalProcessedTime;

                    // Estimate the time remaining for the current file
                    $etaForCurrentFile = ($totalSizeRemaining - $totalProcessedSize) / $averageSpeed;

                    // Estimate the time remaining for the rest of the videos
                    $remainingVideos = $total - $key;
                    $etaForAllVideos = ($averageTimePerVideo * $totalFiles) * $remainingVideos;
                    //echo "averageTimePerVideo($averageTimePerVideo)[$totalProcessedTime / $processedVideosCount]: " . @gmdate("H:i:s", $averageTimePerVideo) . " remainingVideos: " . $remainingVideos . PHP_EOL;

                    // Convert the estimated time into a readable format
                    $months = floor($etaForAllVideos / $secondsInAMonth);
                    $weekSeconds = (int)$etaForAllVideos % (int)$secondsInAMonth;
                    $weeks = floor($weekSeconds / $secondsInAWeek);
                    $daySeconds = (int)$weekSeconds % (int)$secondsInAWeek;
                    $days = floor($daySeconds / $secondsInADay);
                    $hourSeconds = (int)$daySeconds % (int)$secondsInADay;
                    $hours = floor($hourSeconds / $secondsInAnHour);
                    $minuteSeconds = (int)$hourSeconds % (int)$secondsInAnHour;
                    $minutes = floor($minuteSeconds / $secondsInAMinute);
                    $remainingSeconds = (int)$minuteSeconds % $secondsInAMinute;

                    $ETA = "{$months}m {$weeks}w {$days}d {$hours}:{$minutes}:{$remainingSeconds}";

                    echo "$info2 {$timeTakenFormated}s, " . humanFileSize($speed) . "/s ".@gmdate("H:i:s", $etaForCurrentFile);
                    echo  ", Average:" . humanFileSize($averageSpeed) . '/s ';
                    echo "Final ETA: " . $ETA . PHP_EOL;
                } else {
                    echo ("$info2 CDNStorage::APIput same size {$value['remote']['remote_filesize']} {$value['remote']['relative']}") . PHP_EOL;
                }
            } else {
                echo ("{$info1} CDNStorage::APIput not valid local file {$value['local']['local_path']}") . PHP_EOL;
            }
        }
    }
} else {
    die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
}
echo PHP_EOL . " Done! " . PHP_EOL;

die();
