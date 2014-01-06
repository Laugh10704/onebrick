<span class="appTopHeader">One Brick Newsletter Builder
    <div style="float:right; vertical-align:top;">
    <?php if ($stage == 1): ?>
        <img src="<?php print($newsletter_tool_dir); ?>images/150px-Logo.gif" border="0" >
    <?php else: ?>
        <?php echo isSet($_SESSION['regionName']) ? $_SESSION['regionName'] : "NO_NAME__REPORT_THIS_BUG" ?>
    <?php endif; ?>
    </div>
</span>
<div id="errorBox"><?php echo isSet($msg) ? $msg : ""; ?></div>
<br><br>
