<?php if (!defined('SBS_CANVASS_LOADED')) {
    die();
} //Block Direct Access?>
<div class="wrap">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css?ver=4.8.2">
    <link rel="stylesheet" href="/wp-content/plugins/canvass/assets/css/admin/settings.css">
    <h2><?php echo $pageTitle; ?></h2>

    <form method="post" action="<?php print $postTo; ?>">
        <div class="nav-tab-wrapper" style="height:33px;">
            <?php
            $activeTab = null;
            foreach ($tabs as $tabKey => $tabInfo) {
                $activeTab = $tabInfo['active'] ? $tabKey : $activeTab; ?>
                <a href="<?php print $tabInfo['url']; ?>" class="nav-tab <?php print $tabInfo['active'] ? 'nav-tab-active' : ''; ?>"><?php echo $tabInfo['title']; ?></a>
                <?php
            }

            if (!$hideSubmitButton) {
                ?>
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes" style="float:right;">
                <?php
            }
            if ($currentOptionSettings['is_active']) { ?>
                <a class="playpausecontrol playpausecontrol-pause button button-secondary" style="float:right;margin-right:7px;" href="#"><i class="fa fa-pause"></i> Pause</a>
                <input type="hidden" id="SBSCanvass-is_active" name="SBSCanvass[is_active]" value="1">
            <?php } else { ?>
                <a class="playpausecontrol playpausecontrol-play button button-secondary" style="float:right;margin-right:7px;" href="#"><i class="fa fa-play"></i> Resume</a>
                <input type="hidden" id="SBSCanvass-is_active" name="SBSCanvass[is_active]" value="0">
            <?php } ?>
            <script>
            jQuery(function(){
                jQuery('.playpausecontrol').on('click', function(){
                    if (jQuery(this).is('.playpausecontrol-play')) {
                        jQuery('#SBSCanvass-is_active').val('1');
                    } else {
                        jQuery('#SBSCanvass-is_active').val('0');
                    }
                    jQuery('#submit').trigger('click');
                    return false;
                });
            });
            </script>            
        </div>

        <div class="tab-container">
            <?php
            foreach ($tabs as $key => $info) {
                if ($info['active']) {
                    return $this->render('Admin/Partials/Dashboard/BasicSettings/'.str_replace(' ', '', ucwords($key)), $viewData);
                }
            }
            ?>
        </div>
    </form>
</div>
