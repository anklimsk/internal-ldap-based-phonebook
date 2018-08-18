<div class="page index">
<h2><?php echo __d('queue', 'Queue');?></h2>

<h3><?php echo __d('queue', 'Status'); ?></h3>
<?php if ($status) { ?>
<?php
	$running = (time() - $status['time']) < MINUTE;
?>
<?php echo $this->Format->yesNo($running); ?> <?php echo $running ? __d('queue', 'Running') : __d('queue', 'Not running'); ?> (<?php echo __d('queue', 'last %s', $this->Datetime->relLengthOfTime($status['time']))?>)

<?php
	echo '<div><small>Currently '.($status['workers']).' worker(s) total.</small></div>';
?>
<?php } else { ?>
n/a
<?php } ?>

<h3><?php echo __d('queue', 'Queued Tasks'); ?></h3>
<?php
 echo $current;
?> task(s) await processing

<ol>
<?php
foreach ($pendingDetails as $item) {
	echo '<li>'.$item['QueuedTask']['jobtype'] . " (" . $item['QueuedTask']['reference'] . "):";
	echo '<ul>';
		echo '<li>Created: '.$item['QueuedTask']['created'].'</li>';
		echo '<li>Fetched: '.$item['QueuedTask']['fetched'].'</li>';
		echo '<li>Status: '.$item['QueuedTask']['status'].'</li>';
		echo '<li>Progress: '.$this->Number->toPercentage($item['QueuedTask']['progress']).'</li>';
		echo '<li>Failures: '.$item['QueuedTask']['failed'].'</li>';
		echo '<li>Failure Message: '.$item['QueuedTask']['failure_message'].'</li>';
	echo '</ul>';
	echo '</li>';
}
?>
</ol>

<h3><?php echo __d('queue', 'Statistics'); ?></h3>
<ul>
<?php
foreach ($data as $item) {
	echo '<li>'.$item['QueuedTask']['jobtype'] . ":";
	echo '<ul>';
		echo '<li>Finished Jobs in Database: '.$item[0]['num'].'</li>';
		echo '<li>Average Job existence: '.$item[0]['alltime'].'s</li>';
		echo '<li>Average Execution delay: '.$item[0]['fetchdelay'].'s</li>';
		echo '<li>Average Execution time: '.$item[0]['runtime'].'s</li>';
	echo '</ul>';
	echo '</li>';
}
if (empty($data)) {
	echo 'n/a';
}
?>
</ul>

<h3>Settings</h3>
<ul>
<?php
	$configurations = Configure::read('Queue');
	foreach ($configurations as $key => $configuration) {
		echo '<li>';
		if (is_dir($configuration)) {
			$configuration = str_replace(APP, 'APP' . DS, $configuration);
			$configuration = str_replace(DS, '/', $configuration);
		} elseif (is_bool($configuration)) {
			$configuration = $configuration ? 'true' : 'false';
		}
		echo h($key). ': ' . h($configuration);
		echo '</li>';
	}

?>
</ul>
</div>

<div class="actions">
	<ul>
		<li><?php echo $this->Form->postLink(__d('queue', 'Reset %s', __d('queue', 'Queue Tasks')), ['action' => 'reset'], ['confirm' => __d('queue', 'Sure? This will completely reset the queue.')]); ?></li>
	</ul>
</div>
