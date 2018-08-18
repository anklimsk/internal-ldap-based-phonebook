<?php
/**
 * This file is the view file of the plugin. Used for render footer in email.
 *
 * CakeNotify: Sending email from CakePHP using task queues
 * @copyright Copyright 2016, Andrey Klimov.
 */

if (!isset($projectName)) {
    $projectName = '';
}

    $homeUrl = $this->Html->url('/', true);
if (empty($projectName)) {
    $projectName = $homeUrl;
}
?>
<p class="text-right">
    <small>
<?php
    echo nl2br(__d('cake_notify', 'This email was sent %s automatically,
please do not reply to it.', $this->Html->tag(
        'strong',
        $this->Html->link(h($projectName), $homeUrl)
    )));
?>
    </small>
</p>
