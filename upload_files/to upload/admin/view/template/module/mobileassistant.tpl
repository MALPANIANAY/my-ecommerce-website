<style media="screen" type="text/css">
    .perms_group {
        padding: 5px 10px 5px 0px;
        font-weight: bold;
        display: inline-block;
        width: auto;
        text-align: left;
    }

    .perms_group:not(:last-child) {
        margin-bottom: 8px;
    }

    .perms_label {
        margin: 6px 0 0 15px;
        font-weight: normal;
    }

    .content .vtabs .vtabs_links {
        float: none;
        border: none;
        background: none;
        text-align: center;
        padding: 0;
        text-decoration: underline;
        color: #0B0080;
        width: auto;
        font-weight: normal;
    }
</style>
<script type="text/javascript" src="view/javascript/qrcode.min.js"></script>


<?php if ($is_ver20) { ?>

    <?php echo $header; ?><?php echo $column_left; ?>
    <div id="content">
        <div class="page-header">
            <div class="container-fluid">
                <div class="pull-right">
                    <button type="button" id="button_save_continue" form="form_mobassist" data-toggle="tooltip" title="<?php echo $button_save_continue; ?>" class="btn btn-success"><i class="fa fa-check"></i></button>
                    <button type="button" id="button_save_not_continue" form="form_mobassist" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
                    <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
                <h1><?php echo $heading_title; ?></h1>
                <ul class="breadcrumb">
                    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                    <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <div class="container-fluid">
            <?php if ($error_warning) { ?>
            <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php } ?>

            <?php if ($saving_success) { ?>
            <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $saving_success; ?>
                <button type="button" class="close" data-dismiss="alert">×</button>
            </div>
            <?php } ?>

            <?php if ($message_info) { ?>
            <div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo $message_info; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php } ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>

                    <div style="display: inline-block; margin-right: 18px; margin-bottom: 15px; float: right">
                        <span style="margin-right: 15px">
                            <?php echo $module_version; ?> <b><?php echo $connector_version; ?></b>
                        </span>
                        <?php echo $useful_links; ?>
                        <a href="http://platformx.tech" class="link" target="_blank"><?php echo $check_new_version; ?></a> |
                        <a href="https://platformx.tech" class="link" target="_blank"><?php echo $submit_ticket; ?></a> |
                        <a href="http://platformx.tech" class="link" target="_blank"><?php echo $documentation; ?></a>
                    </div>
                </div>
                <div class="panel-body">
                    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form_mobassist" class="form-horizontal">
                        <input type="hidden" name="save_continue" id="save_continue" value="0">
                        <input type="hidden" name="bulk_actions" id="bulk_actions" value="0">

                        <div class="col-sm-2">
                            <ul class="nav nav-pills nav-stacked" id="users">
                                <?php $user_row = 1; ?>
                                <?php foreach ($users as $user) { ?>
                                    <li>
                                        <a href="#tab-user<?php echo $user['user_id']; ?>" data-toggle="tab"><i class="fa fa-minus-circle" onclick="$('#users a[href=\'#tab-user<?php echo $user['user_id']; ?>\']').parent().remove(); $('#tab-user<?php echo $user['user_id'] ?>').remove(); $('#users a:first').tab('show');" title="Delete user"></i> <?php echo $mac_user . ' ' . $user['username']; ?></a>
                                    </li>
                                    <?php $user_row++; ?>
                                <?php } ?>
                                <li id="user-add" style="cursor:pointer;"><a onclick="addUser();"><i class="fa fa-plus-circle"></i> <?php echo $mac_add_user; ?></a></li>
                            </ul>

                            <hr style="margin-top: 30px;">

                            <div style="margin-top: 25px; text-align: center;">
                                <span><?php echo $mac_get_app_from_gp; ?></span>
                                <a class="ma_play" href="platformx.tec/playstore" target="_blank">
                                    <div id="mobassist_app_url_qr" style="margin-top: 7px; margin-bottom: 4px; display: inline-block;"></div><br>
                                    <span><?php echo $mac_click_or_scan_qr; ?></span>
                                </a>
                            </div>

                        </div>

                        <div class="col-sm-10">
                            <div class="tab-content">

                                <?php $user_row = 1; $active_user_row = 1; ?>
                                <?php foreach ($users as $user) {
                                    if(isset($_GET['user_id']) && $user['user_id'] == $_GET['user_id']) {
                                        $active_user_row = $user_row;
                                    } ?>

                                <div class="tab-pane <?php echo $active; ?> " id="tab-user<?php echo $user['user_id']; ?>">
                                    <input type="hidden" name="user[<?php echo $user_row; ?>][user_id]" value="<?php echo $user['user_id']; ?>" />

                                    <div class="form-group">
                                        <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                                        <div class="col-sm-10">
                                            <select name="user[<?php echo $user_row; ?>][user_status]" id="input-status" class="form-control">
                                                <?php if ($user['user_status']) { ?>
                                                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                                <option value="0"><?php echo $text_disabled; ?></option>
                                                <?php } else { ?>
                                                <option value="1"><?php echo $text_enabled; ?></option>
                                                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group required">
                                        <label class="col-sm-2 control-label" for="mobassist_login<?php echo $user_row; ?>"><span data-toggle="tooltip" title="<?php echo $help_login; ?>"><?php echo $entry_login; ?></span></label>
                                        <div class="col-sm-10">
                                            <input type="hidden" id="mobassist_login_old<?php echo $user_row; ?>" value="<?php echo $user['username']; ?>"/>
                                            <input type="text" id="mobassist_login<?php echo $user_row; ?>" class="form-control mobassist_login" data-user_row="<?php echo $user_row; ?>" name="user[<?php echo $user_row; ?>][username]" value="<?php echo $user['username']; ?>" placeholder="<?php echo $entry_login; ?>" required />
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-2 control-label" for="mobassist_pass<?php echo $user_row; ?>"><span data-toggle="tooltip" title="<?php echo $help_pass; ?>"><?php echo $entry_pass; ?></span></label>
                                        <div class="col-sm-10">
                                            <input type="hidden" id="mobassist_pass_old<?php echo $user_row; ?>" value="<?php echo $user['password']; ?>"/>
                                            <input type="password" id="mobassist_pass<?php echo $user_row; ?>" class="form-control mobassist_pass" data-user_row="<?php echo $user_row; ?>" name="user[<?php echo $user_row; ?>][password]" value="<?php echo $user['password']; ?>" placeholder="<?php echo $entry_pass; ?>" />
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-2 control-label" for="mobassist_qr<?php echo $user_row; ?>"><span data-toggle="tooltip" title="<?php echo $help_qr; ?>"><?php echo $entry_qr; ?></span></label>
                                        <div class="col-sm-10">
                                            <div style="position: relative; width: 240px">
                                                <div id="mobassist_qr_code<?php echo $user_row; ?>" class="qr-code" style="display: none;"><?php echo $user['qr_code']; ?></div>
                                                <div id="mobassist_qr_code_changed<?php echo $user_row; ?>" style="display: none; z-index: 1000; text-align: center; position: absolute; top: 0; left: 0; height: 100%;">
                                                    <div style="position: relative; width: 100%; height: 100%;">
                                                        <div style="background: #fff; opacity: 0.9; position: absolute; height: 100%; width: 100%">&nbsp;</div>
                                                        <div style="font-size: 16px; color: #DF0101; width: 100%; text-align: center; padding-top: 45px; position: absolute; font-weight: bold;"><?php echo $error_login_details_changed ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                                $url = "";
                                                if(defined("HTTP_CATALOG")) {
                                                    $url = HTTP_CATALOG;
                                                } else if(defined("HTTP_SERVER")) {
                                                    $url = HTTP_SERVER;
                                                }
                                                if(!empty($url)) {
                                            ?>
                                            <br>
                                            <div>
                                                <a id="qr_code_url<?php echo $user['user_id']; ?>" href="<?php echo $url; ?>?route=module/mobileassistantconnector&call_function=get_qr_code&qr_hash=<?php echo $user['qr_code_hash']; ?>" target="_blank"><?php echo $url; ?>?route=module/mobileassistantconnector&call_function=get_qr_code&qr_hash=<?php echo $user['qr_code_hash']; ?></a>
                                            </div>
                                            <br>
                                            <a data-user_id="<?php echo $user['user_id']; ?>" data-toggle="tooltip" title="" class="btn btn-default change_url" data-original-title="<?php echo $mac_regenerate_hash_url; ?>"><i class="fa fa-refresh"></i> <?php echo $mac_regenerate_hash_url; ?></a>
                                            <?php } ?>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label class="col-sm-2 control-label" for="entry_disable_mis_ord_notif<?php echo $user_row; ?>"><?php echo $entry_disable_mis_ord_notif; ?></label>
                                        <div class="col-sm-10">
                                            <input type="checkbox" id="mobassist_disable_mis_ord_notif<?php echo $user_row; ?>" class="form-control" name="user[<?php echo $user_row; ?>][mobassist_disable_mis_ord_notif]" value="1" <?php echo ($user['mobassist_disable_mis_ord_notif'] == 1 ? 'checked="true"' : ""); ?> />
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo $mac_permissions; ?></label>
                                        <div class="col-sm-10 perms_group" id="user_permissions<?php echo $user_row; ?>">
                                            <div class="perms_group"><?php echo $mac_push_notifications; ?><br/>
                                                <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][push_new_order]" class="perms" value="1" <?php echo (isset($user['allowed_actions']['push_new_order']) ? 'checked="true"' : ""); ?>/> <?php echo $mac_new_order_created; ?></label><br/>
                                                <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][push_order_status_changed]" class="perms" value="1" <?php echo (isset($user['allowed_actions']['push_order_status_changed']) ? 'checked="true"' : ""); ?>/> <?php echo $mac_order_status_changed; ?></label><br/>
                                                <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][push_new_customer]" class="perms" value="1" <?php echo (isset($user['allowed_actions']['push_new_customer']) ? 'checked="true"' : ""); ?>/> <?php echo $mac_new_customer_created; ?></label><br/>
                                            </div>
                                            <br/>
                                            <div class="perms_group"><?php echo $mac_store_statistics; ?><br/>
                                                <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][store_statistics]" class="perms" value="1" <?php echo (isset($user['allowed_actions']['store_statistics']) ? 'checked="true"' : ""); ?>> <?php echo $mac_view_store_statistics; ?></label><br/>
                                            </div>
                                            <br/>
                                            <div class="perms_group"><?php echo $mac_orders; ?><br/>
                                                <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][order_list]" data-user_row="<?php echo $user_row; ?>" class="perms perm_order_list" value="1" <?php echo (isset($user['allowed_actions']['order_list']) ? 'checked="true"' : ""); ?>> <?php echo $mac_view_order_list; ?></label><br/>
                                                <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][order_details]" class="perms perm_order_list<?php echo $user_row; ?>_child" value="1" <?php echo (isset($user['allowed_actions']['order_details']) ? 'checked="true"' : ""); ?>> <?php echo $mac_view_order_details; ?></label><br/>
                                                <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][order_status_updating]" class="perms perm_order_list<?php echo $user_row; ?>_child" value="1" <?php echo (isset($user['allowed_actions']['order_status_updating']) ? 'checked="true"' : ""); ?>> <?php echo $mac_change_order_status; ?></label><br/>
                                            </div>
                                            <br/>
                                            <div class="perms_group"><?php echo $mac_customers; ?><br/>
                                                <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][customer_list]" data-user_row="<?php echo $user_row; ?>" class="perms perm_customer_list" value="1" <?php echo (isset($user['allowed_actions']['customer_list']) ? 'checked="true"' : ""); ?>> <?php echo $mac_view_customer_list; ?></label><br/>
                                                <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][customer_details]" class="perms perm_customer_list<?php echo $user_row; ?>_child" value="1" <?php echo (isset($user['allowed_actions']['customer_details']) ? 'checked="true"' : ""); ?>> <?php echo $mac_view_customer_details; ?></label><br/>
                                            </div>
                                            <br/>
                                            <div class="perms_group"><?php echo $mac_products; ?><br/>
                                                <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][product_list]" data-user_row="<?php echo $user_row; ?>" class="perms perm_product_list" value="1" <?php echo (isset($user['allowed_actions']['product_list']) ? 'checked="true"' : ""); ?>> <?php echo $mac_view_product_list; ?></label><br/>
                                                <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][product_details]" class="perms perm_product_list<?php echo $user_row; ?>_child" value="1" <?php echo (isset($user['allowed_actions']['product_details']) ? 'checked="true"' : ""); ?>> <?php echo $mac_view_product_details; ?></label><br/>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label class="col-sm-2 control-label" for="table_push_devices"><span data-toggle="tooltip" title="<?php echo $push_messages_settings_help; ?>"><?php echo $push_messages_settings; ?></label>
                                        <div class="col-sm-10" id="table_push_devices" style="margin-top: 15px">

                                            <?php $push_devices = $user['devices']; ?>

                                            <table id="mobassistantconnector_devices_table" class="table table-bordered table-hover" style="font-size: 12px;">
                                                <thead>
                                                <tr>
                                                    <td><?php echo $device_name; ?></td>
                                                    <td><?php echo $account_email; ?></td>
                                                    <td><?php echo $last_activity; ?></td>
                                                    <td class="text-right"><?php echo $app_connection_id; ?></td>
                                                    <td><?php echo $store; ?></td>
                                                    <td class="text-center"><?php echo $new_order; ?></td>
                                                    <td class="text-center"><?php echo $new_customer; ?></td>
                                                    <td><?php echo $order_statuses; ?></td>
                                                    <td><?php echo $currency; ?></td>
                                                    <td class="text-center"><?php echo $status; ?></td>
                                                    <td class="text-center"><?php echo $delete; ?></td>
                                                </tr>
                                                </thead>

                                                <tbody class="table_body">
                                                <?php if (count($push_devices)) { ?>
                                                <?php foreach ($push_devices as $device) { ?>
                                                <tr >
                                                    <?php $rowspan = ""; ?>
                                                    <?php $push_settings = $device['push_settings']; ?>
                                                    <?php $rows = count($push_settings); ?>
                                                    <?php if ($rows > 1) $rowspan = 'rowspan="'.$rows.'"'; ?>
                                                    <?php if ($device['device_name'] == "Unknown") { ?>
                                                    <td class="text-center" colspan="3" <?php echo $rowspan; ?> ><?php echo $unknown; ?></td>
                                                    <?php } else { ?>
                                                    <td <?php echo $rowspan; ?> ><?php echo $device['device_name']; ?></td>
                                                    <td <?php echo $rowspan; ?> ><?php echo $device['account_email']; ?></td>
                                                    <td <?php echo $rowspan; ?> ><?php echo $device['last_activity']; ?></td>
                                                    <?php } ?>

                                                    <?php $i = 0; ?>
                                                    <?php foreach ($push_settings as $push_setting) { ?>
                                                    <?php $i++; ?>
                                                    <?php if ($i > 1) { ?><tr><?php } ?>
                                                    <td class="text-right"><?php echo $push_setting['app_connection_id']; ?></td>
                                                    <td><?php echo $push_setting['store_id_name']; ?></td>
                                                    <td class="text-center">
                                                        <?php if ($push_setting['push_new_order'] == 1) {  ?>
                                                        <img src="view/image/mobassistconn_yes.png">
                                                        <?php } else { ?>
                                                        <img src="view/image/mobassistconn_no.png">
                                                        <?php } ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($push_setting['push_new_customer'] == 1) {  ?>
                                                        <img src="view/image/mobassistconn_yes.png">
                                                        <?php } else { ?>
                                                        <img src="view/image/mobassistconn_no.png">
                                                        <?php } ?>
                                                    </td>
                                                    <td><?php echo $push_setting['push_order_statuses_names']; ?></td>
                                                    <td><?php echo $push_setting['push_currency_name']; ?></td>
                                                    <td class="text-center">
                                                        <?php if ($push_setting['status'] == 1) {  ?>
                                                        <a href="<?php echo $action; ?>&disable_setting_id=<?php echo $push_setting['setting_id']; ?>&user_id=<?php echo $user['user_id']; ?>" data-toggle="tooltip" title="" class="btn btn-success" data-original-title="<?php echo $disable; ?>"><i class="fa fa-check"></i></a>
                                                        <?php } else { ?>
                                                        <a href="<?php echo $action; ?>&enable_setting_id=<?php echo $push_setting['setting_id']; ?>&user_id=<?php echo $user['user_id']; ?>" data-toggle="tooltip" title="" class="btn btn-warning" data-original-title="<?php echo $enable; ?>"><i class="fa fa-remove"></i></a>
                                                        <?php } ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="<?php echo $action; ?>&delete_setting_id=<?php echo $push_setting['setting_id']; ?>&user_id=<?php echo $user['user_id']; ?>" onclick="return confirm('<?php echo $are_you_sure; ?>');" data-toggle="tooltip" title="" class="btn btn-danger" data-original-title="<?php echo $delete; ?>"><i class="fa fa-trash-o"></i></a>
                                                    </td>
                                                </tr>
                                                <?php } ?>
                                                <?php } ?>

                                                <?php } else { ?>
                                                <tr><td class="text-center" colspan="12"><?php echo $no_data; ?></td></tr>
                                                <?php } ?>

                                                </tbody>
                                            </table>

                                        </div>

                                    </div>
                                </div>

                            <?php $user_row++; ?>
                            <?php } ?>

                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>


<!-- ========================================================================================================= -->
<!-- ========================================================================================================= -->
<!-- ========================================================================================================= -->
<!-- ========================================================================================================= -->

<?php } else { ?>


    <?php echo $header; ?>
    <div id="content">
        <div class="breadcrumb">
            <?php foreach ($breadcrumbs as $breadcrumb) { ?>
            <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
            <?php } ?>
        </div>
        <?php if ($error_warning) { ?>
            <div class="warning"><?php echo $error_warning; ?></div>
        <?php } ?>
        <?php if ($saving_success) { ?>
            <div class="success"><?php echo $saving_success; ?></div>
        <?php } ?>

        <?php if ($message_info) { ?>
            <div class="warning"><?php echo $message_info; ?></div>
        <?php } ?>

        <div class="box">
            <div class="heading">
                <h1><img src="view/image/module.png" alt="" /> <?php echo $heading_title; ?></h1>
                <div class="buttons"><a id="button_save_continue" class="button"><?php echo $button_save_continue; ?></a> <a id="button_save_not_continue" class="button"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
            </div>
            <div class="content">
                <div class="vtabs">
                    <?php $user_row = 1; ?>
                    <?php foreach ($users as $user) { ?>
                    <a href="#tab-user-<?php echo $user_row; ?>" id="user-<?php echo $user_row; ?>" ><?php echo $mac_user . ' ' . $user['username']; ?>&nbsp;<img src="view/image/delete.png" alt="" onclick="$('#user-<?php echo $user_row; ?>').remove(); $('#tab-user-<?php echo $user_row; ?>').remove(); $('.vtabs a:first:not(.vtabs_links)').trigger('click'); return false;" /></a>
                    <?php $user_row++; ?>
                    <?php } ?>
                    <span id="user-add"><?php echo $mac_add_user; ?>&nbsp;<img src="view/image/add.png" alt="" onclick="addUser();" /></span>

                    <div class="clear" style="clear:both;"></div>

                    <div style="margin-top: 45px; text-align: center;">
                        <div style="margin-right: 10px; font-weight: bold"><?php echo $mac_get_app_from_gp; ?></div>
                        <a class="ma_play vtabs_links" href="https://goo.gl/HyMsF1" target="_blank">
                            <div id="mobassist_app_url_qr" style="margin-top: 3px; margin-bottom: 6px; display: inline-block; margin-right: 10px;"></div>
                            <div style="margin-right: 10px;"><?php echo $mac_click_or_scan_qr; ?></div>
                        </a>

                        <div style="margin-top: 40px; margin-right: 10px">
                            <b><?php echo $useful_links; ?></b><br>
                            <a class="vtabs_links" href="http://platformx.tech" class="link" target="_blank"><?php echo $check_new_version; ?></a>
                            <a class="vtabs_links" href="https://platformx.tech" class="link" target="_blank"><?php echo $submit_ticket; ?></a>
                            <a class="vtabs_links" href="http://platformx.tech" class="link" target="_blank"><?php echo $documentation; ?></a>
                        </div>

                        <div style="margin-top: 40px; margin-right: 10px">
                            <?php echo $module_version; ?> <b><?php echo $connector_version; ?></b>
                        </div>
                    </div>
                </div>


                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form_mobassist">
                    <input type="hidden" name="save_continue" id="save_continue" value="0">
                    <input type="hidden" name="bulk_actions" id="bulk_actions" value="0">

                    <?php $user_row = 1; $active_user_row = 1; ?>
                    <?php foreach ($users as $user) {
                        if(isset($_GET['user_id']) && $user['user_id'] == $_GET['user_id']) {
                            $active_user_row = $user_row;
                        } ?>
                    <div id="tab-user-<?php echo $user_row ?>" class="vtabs-content">

                    <table class="form">
                        <input type="hidden" name="user[<?php echo $user_row; ?>][user_id]" value="<?php echo $user['user_id']; ?>" />
                        <tr>
                            <td><?php echo $entry_status; ?></td>
                            <td>
                                <select name="user[<?php echo $user_row; ?>][user_status]" id="input-status" class="form-control">
                                    <?php if ($user['user_status']) { ?>
                                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                    <option value="0"><?php echo $text_disabled; ?></option>
                                    <?php } else { ?>
                                    <option value="1"><?php echo $text_enabled; ?></option>
                                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td><span class="required">*</span> <?php echo $entry_login; ?><br /><span class="help">(<?php echo $help_login; ?>)</span></td>
                            <td>
                                <input type="hidden" id="mobassist_login_old<?php echo $user_row; ?>" value="<?php echo $user['username']; ?>"/>
                                <input type="text" id="mobassist_login<?php echo $user_row; ?>" class="form-control mobassist_login" data-user_row="<?php echo $user_row; ?>" name="user[<?php echo $user_row; ?>][username]" value="<?php echo $user['username']; ?>" placeholder="<?php echo $entry_login; ?>" required />
                            </td>
                        </tr>

                        <tr>
                            <td><?php echo $entry_pass; ?><br /><span class="help">(<?php echo $help_pass; ?>)</span></td>
                            <td>
                                <input type="hidden" id="mobassist_pass_old<?php echo $user_row; ?>" value="<?php echo $user['password']; ?>"/>
                                <input type="password" id="mobassist_pass<?php echo $user_row; ?>" class="form-control mobassist_pass" data-user_row="<?php echo $user_row; ?>" name="user[<?php echo $user_row; ?>][password]" value="<?php echo $user['password']; ?>" placeholder="<?php echo $entry_pass; ?>" />
                            </td>
                        </tr>


                        <tr>
                            <td><?php echo $entry_qr; ?><br /><span class="help">(<?php echo $help_qr; ?>)</span></td>
                            <td>
                                <div style="position: relative; width: 240px">
                                    <div id="mobassist_qr_code<?php echo $user_row; ?>" class="qr-code" style="display: none;"><?php echo $user['qr_code']; ?></div>
                                    <div id="mobassist_qr_code_changed<?php echo $user_row; ?>" style="display: none; z-index: 1000; text-align: center; position: absolute; top: 0; left: 0; height: 100%;">
                                        <div style="position: relative; width: 100%; height: 100%;">
                                            <div style="background: #fff; opacity: 0.9; position: absolute; height: 100%; width: 100%">&nbsp;</div>
                                            <div style="font-size: 16px; color: #DF0101; width: 100%; text-align: center; padding-top: 45px; position: absolute; font-weight: bold;"><?php echo $error_login_details_changed ?></div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                    $url = "";
                                    if(defined("HTTP_CATALOG")) {
                                        $url = HTTP_CATALOG;
                                    } else if(defined("HTTP_SERVER")) {
                                        $url = HTTP_SERVER;
                                    }
                                    if(!empty($url)) {
                                ?>
                                <br>
                                <div>
                                    <a id="qr_code_url<?php echo $user['user_id']; ?>" href="<?php echo $url; ?>?route=module/mobileassistantconnector&call_function=get_qr_code&qr_hash=<?php echo $user['qr_code_hash']; ?>" target="_blank"><?php echo $url; ?>?route=module/mobileassistantconnector&call_function=get_qr_code&qr_hash=<?php echo $user['qr_code_hash']; ?></a>
                                </div>
                                <br>
                                <a data-user_id="<?php echo $user['user_id']; ?>" class="button change_url" title="<?php echo $mac_regenerate_hash_url; ?>"><?php echo $mac_regenerate_hash_url; ?></a> 
                                <?php } ?>
                            </td>
                        </tr>

                        <tr>
                            <td><?php echo $entry_disable_mis_ord_notif; ?></td>
                            <td><input type="checkbox" id="mobassist_disable_mis_ord_notif<?php echo $user_row; ?>" class="form-control" name="user[<?php echo $user_row; ?>][mobassist_disable_mis_ord_notif]" value="1" <?php echo ($user['mobassist_disable_mis_ord_notif'] == 1 ? 'checked="true"' : ""); ?> /></td>
                        </tr>


                        <tr>
                            <td><?php echo $mac_permissions; ?></td>
                            <td>
                                <div class="perms_group" id="user_permissions<?php echo $user_row; ?>">
                                    <div class="perms_group"><?php echo $mac_push_notifications; ?><br/>
                                        <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][push_new_order]" class="perms" value="1" <?php echo (isset($user['allowed_actions']['push_new_order']) ? 'checked="true"' : ""); ?>/> <?php echo $mac_new_order_created; ?></label><br/>
                                        <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][push_order_status_changed]" class="perms" value="1" <?php echo (isset($user['allowed_actions']['push_order_status_changed']) ? 'checked="true"' : ""); ?>/> <?php echo $mac_order_status_changed; ?></label><br/>
                                        <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][push_new_customer]" class="perms" value="1" <?php echo (isset($user['allowed_actions']['push_new_customer']) ? 'checked="true"' : ""); ?>/> <?php echo $mac_new_customer_created; ?></label><br/>
                                    </div>
                                    <br/>
                                    <div class="perms_group"><?php echo $mac_store_statistics; ?><br/>
                                        <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][store_statistics]" class="perms" value="1" <?php echo (isset($user['allowed_actions']['store_statistics']) ? 'checked="true"' : ""); ?>> <?php echo $mac_view_store_statistics; ?></label><br/>
                                    </div>
                                    <br/>
                                    <div class="perms_group"><?php echo $mac_orders; ?><br/>
                                        <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][order_list]" data-user_row="<?php echo $user_row; ?>" class="perms perm_order_list" value="1" <?php echo (isset($user['allowed_actions']['order_list']) ? 'checked="true"' : ""); ?>> <?php echo $mac_view_order_list; ?></label><br/>
                                        <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][order_details]" class="perms perm_order_list<?php echo $user_row; ?>_child" value="1" <?php echo (isset($user['allowed_actions']['order_details']) ? 'checked="true"' : ""); ?>> <?php echo $mac_view_order_details; ?></label><br/>
                                        <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][order_status_updating]" class="perms perm_order_list<?php echo $user_row; ?>_child" value="1" <?php echo (isset($user['allowed_actions']['order_status_updating']) ? 'checked="true"' : ""); ?>> <?php echo $mac_change_order_status; ?></label><br/>
                                    </div>
                                    <br/>
                                    <div class="perms_group"><?php echo $mac_customers; ?><br/>
                                        <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][customer_list]" data-user_row="<?php echo $user_row; ?>" class="perms perm_customer_list" value="1" <?php echo (isset($user['allowed_actions']['customer_list']) ? 'checked="true"' : ""); ?>> <?php echo $mac_view_customer_list; ?></label><br/>
                                        <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][customer_details]" class="perms perm_customer_list<?php echo $user_row; ?>_child" value="1" <?php echo (isset($user['allowed_actions']['customer_details']) ? 'checked="true"' : ""); ?>> <?php echo $mac_view_customer_details; ?></label><br/>
                                    </div>
                                    <br/>
                                    <div class="perms_group"><?php echo $mac_products; ?><br/>
                                        <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][product_list]" data-user_row="<?php echo $user_row; ?>" class="perms perm_product_list" value="1" <?php echo (isset($user['allowed_actions']['product_list']) ? 'checked="true"' : ""); ?>> <?php echo $mac_view_product_list; ?></label><br/>
                                        <label class="perms_label"><input type="checkbox" name="user[<?php echo $user_row; ?>][allowed_actions][product_details]" class="perms perm_product_list<?php echo $user_row; ?>_child" value="1" <?php echo (isset($user['allowed_actions']['product_details']) ? 'checked="true"' : ""); ?>> <?php echo $mac_view_product_details; ?></label><br/>
                                    </div>
                                </div>
                            </td>
                        </tr>


                        <tr>
                            <td><?php echo $push_messages_settings; ?><br /><span class="help">(<?php echo $push_messages_settings_help; ?>)</span></td>
                            <td>
                                <?php $push_devices = $user['devices']; ?>
                                <table id="mobassistantconnector_devices_table" class="list" style="font-size: 12px;">
                                    <thead>
                                    <tr>
                                        <td class="left"><?php echo $device_name; ?></td>
                                        <td class="left"><?php echo $account_email; ?></td>
                                        <td class="left"><?php echo $last_activity; ?></td>
                                        <td class="right"><?php echo $app_connection_id; ?></td>
                                        <td class="left"><?php echo $store; ?></td>
                                        <td class="center"><?php echo $new_order; ?></td>
                                        <td class="center"><?php echo $new_customer; ?></td>
                                        <td class="left"><?php echo $order_statuses; ?></td>
                                        <td class="left"><?php echo $currency; ?></td>
                                        <td class="center"><?php echo $status; ?></td>
                                        <td class="center"><?php echo $delete; ?></td>
                                    </tr>
                                    </thead>

                                    <tbody class="table_body">
                                    <?php if (count($push_devices)) { ?>
                                    <?php foreach ($push_devices as $device) { ?>
                                    <tr >
                                        <?php $rowspan = ""; ?>
                                        <?php $push_settings = $device['push_settings']; ?>
                                        <?php $rows = count($push_settings); ?>
                                        <?php if ($rows > 1) $rowspan = 'rowspan="'.$rows.'"'; ?>
                                        <?php if ($device['device_name'] == "Unknown") { ?>
                                            <td class="center" colspan="3" <?php echo $rowspan; ?> ><?php echo $unknown; ?></td>
                                        <?php } else { ?>
                                            <td class="left" <?php echo $rowspan; ?> ><?php echo $device['device_name']; ?></td>
                                            <td class="left" <?php echo $rowspan; ?> ><?php echo $device['account_email']; ?></td>
                                            <td class="left" <?php echo $rowspan; ?> ><?php echo $device['last_activity']; ?></td>
                                        <?php } ?>

                                        <?php $i = 0; ?>
                                        <?php foreach ($push_settings as $push_setting) { ?>
                                        <?php $i++; ?>
                                        <?php if ($i > 1) { ?><tr><?php } ?>
                                        <td class="right"><?php echo $push_setting['app_connection_id']; ?></td>
                                        <td class="left"><?php echo $push_setting['store_id_name']; ?></td>
                                        <td class="center">
                                            <?php if ($push_setting['push_new_order'] == 1) {  ?>
                                                <img src="view/image/mobassistconn_yes.png">
                                            <?php } else { ?>
                                                <img src="view/image/mobassistconn_no.png">
                                            <?php } ?>
                                        </td>
                                        <td class="center">
                                            <?php if ($push_setting['push_new_customer'] == 1) {  ?>
                                                <img src="view/image/mobassistconn_yes.png">
                                            <?php } else { ?>
                                                <img src="view/image/mobassistconn_no.png">
                                            <?php } ?>

                                        </td>
                                        <td class="left"><?php echo $push_setting['push_order_statuses_names']; ?></td>
                                        <td class="left"><?php echo $push_setting['push_currency_name']; ?></td>
                                        <td class="center">
                                            <?php if ($push_setting['status'] == 1) {  ?>
                                                <a href="<?php echo $action; ?>&disable_setting_id=<?php echo $push_setting['setting_id']; ?>&user_id=<?php echo $user['user_id']; ?>" class="button" style="background-color: #75a74d; border-color: #5c843d;" title="<?php echo $disable; ?>" ><?php echo $enabled; ?></a>
                                            <?php } else { ?>
                                                <a href="<?php echo $action; ?>&enable_setting_id=<?php echo $push_setting['setting_id']; ?>&user_id=<?php echo $user['user_id']; ?>" class="button" style="background-color: #f38733; border-color: #ef710e;" title="<?php echo $enable; ?>" ><?php echo $disabled; ?></a>
                                            <?php } ?>
                                        </td>
                                        <td class="center">
                                            <a class="button" style="background-color: #f56b6b; border-color: #f24545;" href="<?php echo $action; ?>&delete_setting_id=<?php echo $push_setting['setting_id']; ?>&user_id=<?php echo $user['user_id']; ?>" onclick="return confirm('<?php echo $are_you_sure; ?>')"><?php echo $delete; ?></a>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                    <?php } ?>

                                    <?php } else { ?>
                                    <tr><td colspan="11" class="center"><?php echo $no_data; ?></td></tr>
                                    <?php } ?>

                                    </tbody>
                                </table>

                            </td>
                        </tr>
                    </table>
                    </div>
                    <?php $user_row++; ?>
                    <?php } ?>
                </form>
            </div>
        </div>
    </div>

<?php } ?>

<script type="text/javascript"><!--
    $(document).ready(function() {
        $('.qr-code').each(function(i, obj) {
            var qrdata = $(obj).html();
            $(obj).html(" ");
            var qrcode = new QRCode(obj, {
                width : 240,
                height : 240
            });
            qrcode.makeCode(qrdata);
            $(obj).show();
        });


        setTimeout(
            function() {
                $('.success').hide('fast');
                $('.alert-success').hide('fast');
            },
            3000
        );

        $('#button_save_continue').on('click', function () {
            $('#save_continue').val('1');
            $('#form_mobassist').submit();
        });


        $('#btn_enable_selected').on('click', function () {
            if($( "#mobassistantconnector_devices_table tbody input[type=checkbox]:checked" ).length <= 0) {
                alert("<?php echo $please_select_push_settings; ?>");
                return false;
            }
            $('#save_continue').val('1');
            $('#bulk_actions').val('1');
            $('#form_mobassist').submit();
            return false;
        });

        $('#btn_disable_selected').on('click', function () {
            if($( "#mobassistantconnector_devices_table tbody input[type=checkbox]:checked" ).length <= 0) {
                alert("<?php echo $please_select_push_settings; ?>");
                return false;
            }
            $('#save_continue').val('1');
            $('#bulk_actions').val('2');
            $('#form_mobassist').submit();
            return false;
        });

        $('#btn_delete_selected').on('click', function () {
            if($( "#mobassistantconnector_devices_table tbody input[type=checkbox]:checked" ).length <= 0) {
                alert("<?php echo $please_select_push_settings; ?>");
                return false;
            }
            if (confirm('Are you sure?')) {
                $('#save_continue').val('1');
                $('#bulk_actions').val('3');
                $('#form_mobassist').submit();
            }
            return false;
        });


        $('#button_save_not_continue').on('click', function () {
            $('#save_continue').val('0');
            $('#form_mobassist').submit();
        });

        var onCredentChange = function(user_row) {
            if(user_row <= 0 || user_row == undefined) return;

            var old_login = $("#mobassist_login_old" + user_row).val();
            var old_pass = $("#mobassist_pass_old" + user_row).val();
            var mobassist_login = $("#mobassist_login" + user_row);
            var mobassist_pass = $("#mobassist_pass" + user_row);

            var mobassist_qr_code_changed = $('#mobassist_qr_code_changed' + user_row);

            if(old_login != $(mobassist_login).val() || old_pass != $(mobassist_pass).val()) {
                var qr = $("#mobassist_qr_code" + user_row);
                if($(qr).width() > 0 && $(qr).attr('src') != '') {
                    $(mobassist_qr_code_changed).width($(qr).width()).show('fast');
                } else {
                    $(mobassist_qr_code_changed).hide('fast');
                }
            } else {
                $(mobassist_qr_code_changed).hide('fast');
            }
        };

        $(".mobassist_login, .mobassist_pass").on('keyup', function () {
            var user_row = $(this).data("user_row");
            onCredentChange(user_row);
        });


        <?php
        $url = "";
        if(defined("HTTP_CATALOG")) {
            $url = HTTP_CATALOG;
        } else if(defined("HTTP_SERVER")) {
            $url = HTTP_SERVER;
        }
        ?>
        var store_url_qr = "<?php echo $url; ?>?route=module/mobileassistantconnector&call_function=get_qr_code&qr_hash=";

        $(".change_url").on('click', function () {
            var user_id = $(this).data("user_id");

            $.ajax({
                url:"index.php?route=module/mobileassistantconnector&call_function=generate_qr_code_hash&token=<?php echo $token; ?>&user_id="+user_id,
                dataType: 'json',
                success:function(res) {
                    $("#qr_code_url"+user_id).attr("href", store_url_qr+res.qr_code_hash);
                    $("#qr_code_url"+user_id).text(store_url_qr+res.qr_code_hash);
                }
            });
        });


        var disableChildCheckbox = function(el, el_class) {
            var user_row = $(el).data("user_row");
            if(!el.checked) $('.' + el_class + user_row + '_child').prop('checked', false);
            $('.' + el_class + user_row + '_child').prop('disabled', !el.checked);
        }

        $(document).on('change', '.perm_order_list', function () {
            disableChildCheckbox(this, 'perm_order_list');
        });

        $(document).on('change', '.perm_customer_list', function () {
            disableChildCheckbox(this, 'perm_customer_list');
        });

        $(document).on('change', '.perm_product_list', function () {
            disableChildCheckbox(this, 'perm_product_list');
        });

        var qrcode = new QRCode(document.getElementById('mobassist_app_url_qr'), {
            width : 130,
            height : 130
        });
        qrcode.makeCode('https://goo.gl/HyMsF1');

        <?php if ($is_ver20) { ?>
            $('#users a[href="#tab-user<?php echo $active_user_row; ?>"]').tab('show');
        <?php } else { ?>
            $('.vtabs a:not(.vtabs_links)').tabs();
            $('#user-<?php echo $active_user_row; ?>').trigger('click');
        <?php } ?>
    });



    var user_row = <?php echo $user_row; ?>;

    function addUser() {
        var html  = '';
        <?php if ($is_ver20) { ?>
        html += '<div class="tab-pane" id="tab-user' + user_row + '">';
        html += '  <input type="hidden" name="user[' + user_row + '][user_id]" value="" />';

        html += '  <div class="form-group">';
        html += '    <label class="col-sm-2 control-label" for="input-status' + user_row + '"><?php echo $entry_status; ?></label>';
        html += '    <div class="col-sm-10">';
        html += '      <select name="user[' + user_row + '][user_status]" id="input-status' + user_row + '" class="form-control">';
        html += '        <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
        html += '        <option value="0"><?php echo $text_disabled; ?></option>';
        html += '      </select>';
        html += '    </div>';
        html += '  </div>';

        html += '  <div class="form-group required">';
        html += '    <label class="col-sm-2 control-label" for="mobassist_login' + user_row + '"><span data-toggle="tooltip" title="<?php echo $help_login; ?>"><?php echo $entry_login; ?></span></label>';
        html += '    <div class="col-sm-10"><input type="text" id="mobassist_login' + user_row + '" name="user[' + user_row + '][username]" value="1" placeholder="<?php echo $entry_login; ?>" required class="form-control" /></div>';
        html += '  </div>';

        html += '  <div class="form-group">';
        html += '    <label class="col-sm-2 control-label" for="mobassist_pass' + user_row + '"><span data-toggle="tooltip" title="<?php echo $help_pass; ?>"><?php echo $entry_pass; ?></span></label>';
        html += '    <div class="col-sm-10"><input type="password" id="mobassist_pass' + user_row + '" name="user[' + user_row + '][password]" value="<?php echo md5(1); ?>" placeholder="<?php echo $entry_pass; ?>" class="form-control" /></div>';
        html += '  </div>';

        html += '  <div class="form-group">';
        html += '    <label class="col-sm-2 control-label" for="entry_disable_mis_ord_notif' + user_row + '"><?php echo $entry_disable_mis_ord_notif; ?></label>';
        html += '    <div class="col-sm-10"><input type="checkbox" id="mobassist_disable_mis_ord_notif' + user_row + '" class="form-control" name="user[' + user_row + '][mobassist_disable_mis_ord_notif]" value="1" /></div>';
        html += '  </div>';

        html += '  <div class="form-group">';
        html += '    <label class="col-sm-2 control-label" for="permissions<?php echo $user_row; ?>">Permissions</label>';
        html += '    <div class="col-sm-10 perms_group" id="user_permissions">';
        html += '      <div class="perms_group"><?php echo $mac_push_notifications; ?><br/>';
        html += '        <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][push_new_order]" class="perms" value="1" checked/> <?php echo $mac_new_order_created; ?></label><br/>';
        html += '        <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][push_order_status_changed]" class="perms" value="1" checked/> <?php echo $mac_order_status_changed; ?></label><br/>';
        html += '        <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][push_new_customer]" class="perms" value="1" checked/> <?php echo $mac_new_customer_created; ?></label><br/>';
        html += '      </div>';
        html += '      <br/>';
        html += '      <div class="perms_group">Store statistics<br/>';
        html += '        <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][store_statistics]" class="perms" value="1" checked> <?php echo $mac_view_store_statistics; ?></label><br/>';
        html += '      </div>';
        html += '      <br/>';
        html += '      <div class="perms_group"><?php echo $mac_orders; ?><br/>';
        html += '        <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][order_list]" data-user_row="' + user_row + '" class="perms perm_order_list" value="1" checked> <?php echo $mac_view_order_list; ?></label><br/>';
        html += '        <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][order_details]" class="perms perm_order_list' + user_row + '_child" value="1" checked> <?php echo $mac_view_order_details; ?></label><br/>';
        html += '        <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][order_status_updating]" class="perms perm_order_list' + user_row + '_child" value="1" checked> <?php echo $mac_change_order_status; ?></label><br/>';
        html += '      </div>';
        html += '      <br/>';
        html += '      <div class="perms_group"><?php echo $mac_customers; ?><br/>';
        html += '        <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][customer_list]" data-user_row="' + user_row + '" class="perms perm_customer_list" value="1" checked> <?php echo $mac_view_customer_list; ?></label><br/>';
        html += '        <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][customer_details]" class="perms perm_customer_list' + user_row + '_child" value="1" checked> <?php echo $mac_view_customer_details; ?></label><br/>';
        html += '      </div>';
        html += '      <br/>';
        html += '      <div class="perms_group"><?php echo $mac_products; ?><br/>';
        html += '        <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][product_list]" data-user_row="' + user_row + '" class="perms perm_product_list" value="1" checked> <?php echo $mac_view_product_list; ?></label><br/>';
        html += '        <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][product_details]" class="perms perm_product_list' + user_row + '_child" value="1" checked> <?php echo $mac_view_product_details; ?></label><br/>';
        html += '      </div>';
        html += '    </div>';
        html += '  </div>';
        html += '</div>';

        $('#form_mobassist .tab-content').append(html);

        $('#user-add').before('<li><a href="#tab-user' + user_row + '" data-toggle="tab"><i class="fa fa-minus-circle" onclick="$(\'#users a:first\').tab(\'show\'); $(\'a[href=\\\'#tab-user' + user_row + '\\\']\').parent().remove(); $(\'#tab-user' + user_row + '\').remove();"></i> <?php echo $mac_user; ?> 1</a></li>');

        $('#users a[href="#tab-user' + user_row + '"]').tab('show');


        <?php } else { ?>
        html += '<div id="tab-user-' + user_row + '" class="vtabs-content">';
        html += '  <input type="hidden" name="user[' + user_row + '][user_id]" value="" />';
        html += '  <table class="form">';
        html += '    <tr>';
        html += '      <td><?php echo $entry_status; ?></td>';
        html += '      <td>';
        html += '        <select name="user[' + user_row + '][user_status]" id="input-status' + user_row + '" class="form-control">';
        html += '          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
        html += '          <option value="0"><?php echo $text_disabled; ?></option>';
        html += '        </select>';
        html += '      </td>';
        html += '    </tr>';
        html += '    <tr>';
        html += '      <td><span class="required">*</span> <?php echo $entry_login; ?><br /><span class="help">(<?php echo $help_login; ?>)</span></td>';
        html += '      <td><input type="text" id="mobassist_login' + user_row + '" name="user[' + user_row + '][username]" value="1" placeholder="<?php echo $entry_login; ?>" required class="form-control" /></td>';
        html += '    </tr>';
        html += '    <tr>';
        html += '      <td><?php echo $entry_pass; ?><br /><span class="help">(<?php echo $help_pass; ?>)</span></td>';
        html += '      <td><input type="password" id="mobassist_pass' + user_row + '" name="user[' + user_row + '][password]" value="<?php echo md5(1); ?>" placeholder="<?php echo $entry_pass; ?>" class="form-control" /></td>';
        html += '    </tr>';
        html += '    <tr>';
        html += '      <td><?php echo $entry_disable_mis_ord_notif; ?></td>';
        html += '      <td><input type="checkbox" id="mobassist_disable_mis_ord_notif' + user_row + '" class="form-control" name="user[' + user_row + '][mobassist_disable_mis_ord_notif]" value="1" /></td>';
        html += '    </tr>';
        html += '    <tr>';
        html += '      <td>Permissions</td>';
        html += '      <td>';
        html += '        <div class="perms_group" id="user_permissions">';
        html += '          <div class="perms_group"><?php echo $mac_push_notifications; ?><br/>';
        html += '            <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][push_new_order]" class="perms" value="1" checked/> <?php echo $mac_new_order_created; ?></label><br/>';
        html += '            <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][push_order_status_changed]" class="perms" value="1" checked/> <?php echo $mac_order_status_changed; ?></label><br/>';
        html += '            <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][push_new_customer]" class="perms" value="1" checked/> <?php echo $mac_new_customer_created; ?></label><br/>';
        html += '          </div>';
        html += '          <br/>';
        html += '          <div class="perms_group"><?php echo $mac_store_statistics; ?><br/>';
        html += '            <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][store_statistics]" class="perms" value="1" checked> <?php echo $mac_view_store_statistics; ?></label><br/>';
        html += '          </div>';
        html += '          <br/>';
        html += '          <div class="perms_group"><?php echo $mac_orders; ?><br/>';
        html += '            <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][order_list]" data-user_row="' + user_row + '" class="perms perm_order_list" value="1" checked> <?php echo $mac_view_order_list; ?></label><br/>';
        html += '            <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][order_details]" class="perms perm_order_list' + user_row + '_child" value="1" checked> <?php echo $mac_view_order_details; ?></label><br/>';
        html += '            <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][order_status_updating]" class="perms perm_order_list' + user_row + '_child" value="1" checked> <?php echo $mac_change_order_status; ?></label><br/>';
        html += '          </div>';
        html += '          <br/>';
        html += '          <div class="perms_group"><?php echo $mac_customers; ?><br/>';
        html += '            <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][customer_list]" data-user_row="' + user_row + '" class="perms perm_customer_list" value="1" checked> <?php echo $mac_view_customer_list; ?></label><br/>';
        html += '            <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][customer_details]" class="perms perm_customer_list' + user_row + '_child" value="1" checked> <?php echo $mac_view_customer_details; ?></label><br/>';
        html += '          </div>';
        html += '          <br/>';
        html += '          <div class="perms_group"><?php echo $mac_products; ?><br/>';
        html += '            <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][product_list]" data-user_row="' + user_row + '" class="perms perm_product_list" value="1" checked> <?php echo $mac_view_product_list; ?></label><br/>';
        html += '            <label class="perms_label"><input type="checkbox" name="user[' + user_row + '][allowed_actions][product_details]" class="perms perm_product_list' + user_row + '_child" value="1" checked> <?php echo $mac_view_product_details; ?></label><br/>';
        html += '          </div>';
        html += '        </div>';
        html += '      </td>';
        html += '    </tr>';
        html += '  </table>';
        html += '</div>';

        $('#form_mobassist').append(html);

        $('#user-add').before('<a href="#tab-user-' + user_row + '" id="module-' + user_row + '"><?php echo $mac_user; ?> 1&nbsp;<img src="view/image/delete.png" alt="" onclick="$(\'.vtabs a:first\').trigger(\'click\'); $(\'#user-' + user_row + '\').remove(); $(\'#tab-user-' + user_row + '\').remove(); return false;" /></a>');

        $('.vtabs a:not(.vtabs_links)').tabs();

        $('#user-' + user_row).trigger('click');

        <?php } ?>

        user_row++;
    }

    //--></script>

<?php echo $footer; ?>