<?php
/**
 * 404 error page
 *
 * @package   MyAAC
 * @author    Slawkens <slawkens@gmail.com>
 * @copyright 2017 MyAAC
 * @version   0.6.6
 * @link      http://my-aac.org
 */
defined('MYAAC') or die('Direct access not allowed!');
$title = '404 Not Found';

header('HTTP/1.0 404 Not Found');
?>
<h1>Not Found</h1>
<p>The requested URL <?php echo $_SERVER['REQUEST_URI']; ?> was not found on this server.</p>
