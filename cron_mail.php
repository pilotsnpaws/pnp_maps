<HTML>
<?php
/**
*
* @package phpBB3
* @version $Id: cron.php 8479 2008-03-29 00:22:48Z naderman $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
*/
define('IN_PHPBB', true);
define('IN_CRON', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$to_email = 'michaeljgreen@gmail.com' ;
$subject_email = 'pnp cron_mail running' ;
$headers = 'From: forum@pilotsnpaws.org' . "\r\n" .
    'Reply-To: michaeljgreen@gmail.com' . "\r\n" .
    'MIME-Version: 1.0' . "\r\n" .
    'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

// Flush here to prevent browser from showing the page as loading while running cron.
//flush();


	echo '<div>' ;
	echo 'email package size: ', $config['email_package_size'] ;
	echo '</div>' ;
	echo '<div>' ;
	echo 'start time: ', date("D M d, Y h:i:s a"); 
	$queue_formatted_before = number_format(filesize('cache/queue.php'),0,'.',',') ; 
	echo '<div>queue.php before: ', $queue_formatted_before, ' bytes' ;
	echo '</div>' ;
	echo '<div>' ;
	echo 'queue is running from cron_mail.php, last_queue_run = ',$config['last_queue_run'] ;
	echo '</div>' ;

	echo '<div>' ;
	echo 'func messenger: ', date("D M d, Y h:i:s a"); 
	echo '</div>' ;
	Include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
	$queue = new queue();

	echo '<div>' ;
	echo 'start process: ', date("D M d, Y h:i:s a"); 
	echo '</div>' ;
	$queue->process();
	
	echo '<div>' ;
	$queue_formatted_after = number_format(filesize('cache/queue.php'),0,'.',',') ; 
	echo '<div>queue.php after: ', $queue_formatted_after, ' bytes' ;
	echo '</div>' ;
	echo 'end time: ', date("D M d, Y h:i:s a"); 
	

$email_message = '	<html>
				<head>
				  <title>pnp cron_mail running</title>
				  </head>
				<body>
				<div>
					email package size: ' . $config['email_package_size'] .
				'</div>
				<div>
					start time: ' . date("D M d, Y h:i:s a") .
					'<div>queue.php before: ' .
					$queue_formatted_before . ' bytes
				</div>
				<div>
					queue is running from cron_mail.php, last_queue_run = ' .
					$config['last_queue_run']  .
				'</div>
				<div>
					func messenger: ' .
					date("D M d, Y h:i:s a") .
				'</div> 
				<div>
					start process: ' . 
					date("D M d, Y h:i:s a") .
				'</div> 
				<div>
				<div>queue.php after: ' .
				$queue_formatted_after .
				' bytes
				</div>
				end time: ' .
				date("D M d, Y h:i:s a") .
				'</body></html>'	
				; 


	mail($to_email,$subject_email,$email_message,$headers);

?>
</HTML>