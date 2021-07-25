<?php if (!defined('SBS_CANVASS_LOADED')) {
    die();
} //Block Direct Access?>

<h3>Canvass Setup</h3>
<div class="SBS-input-wrapper">
    <label for="SBSCanvass-oem" class="SBS-label">Starting Address</label>
    <input type="text" class="SBS-text" name="SBSCanvass[starting_address]" value="<?php echo $currentOptionSettings['starting_address']; ?>" id="SBSCanvass-starting_address">
</div>
<div class="SBS-input-wrapper">
    <label for="SBSCanvass-oem" class="SBS-label">Bing API Key</label>
    <input type="text" class="SBS-text" name="SBSCanvass[bing_api_key]" value="<?php echo $currentOptionSettings['bing_api_key']; ?>" id="SBSCanvass-bing_api_key">
</div>


<script>
jQuery(function(){
    jQuery('LABEL[data-functions="panel-switch"]').on('mouseup', function(e){
        e.stopPropagation();
        var $checkbox = $(this).find('INPUT');
        setTimeout(function() {
            var $panel = $checkbox.parent().parent().parent().parent().next();
            if ($checkbox.prop('checked'))
            {
                $panel.removeClass('switch-panel-hidden');
            }
            else
            {
                $panel.addClass('switch-panel-hidden');
            }

        }, 200);
    });
});
</script>