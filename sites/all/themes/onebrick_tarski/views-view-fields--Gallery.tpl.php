<div class="event-gallery-img">
<div class="photos">

<?php
print $fields['field_event_photos_1']->content;
?>
</div>
<div class="info">
<ul>
<?php
print "<li class='event-title'>".$fields['title']->content."</li>";
print "<li class='photo-count'>".$fields['field_event_photos']->content." photos</li>";
print "<li>".$fields['field_event_date']->content."</li>";
?>
</ul>
</div>
</div>
