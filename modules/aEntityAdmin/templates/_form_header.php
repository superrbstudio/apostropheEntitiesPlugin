<?php include_partial('aEntityAdmin/subnav') ?>
<?php a_js_call('aMultipleSelect(?, ?)', '.a-admin-form-field-blog_posts', array('autocomplete' => a_url('aBlogAdmin', 'search'))) ?>
<?php a_js_call('aMultipleSelect(?, ?)', '.a-admin-form-field-events', array('autocomplete' => a_url('aEventAdmin', 'search'))) ?>
<?php a_js_call('aMultipleSelectAll(?)', array('choose-one' => a_('Select to Add'))) ?>
