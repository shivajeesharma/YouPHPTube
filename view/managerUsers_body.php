<?php
$users_tabs = [];

$users_tabs[] = ['selector' => 'grid', 'queryString' => '?status=a', 'icon' => 'fas fa-user', 'title' => 'Active Users', 'active' => 'active', 'userGroupID' => 0];
$users_tabs[] = ['selector' => 'gridInactive', 'queryString' => '?status=i', 'icon' => 'fas fa-user-slash', 'title' => 'Inactive Users', 'active' => '', 'userGroupID' => 0];
$users_tabs[] = ['selector' => 'gridAdmin', 'queryString' => '?isAdmin=1', 'icon' => 'fas fa-user-tie', 'title' => 'Admin Users', 'active' => '', 'userGroupID' => 0];

if (empty($advancedCustomUser->disableCompanySignUp)) {
    $users_tabs[] = ['selector' => 'companyAdmin', 'queryString' => '?isCompany=1', 'icon' => 'fas fa-building', 'title' => 'Company Users', 'active' => '', 'userGroupID' => 0];
    $users_tabs[] = ['selector' => 'companyApAdmin', 'queryString' => '?isCompany=2', 'icon' => 'fas fa-building', 'title' => 'Company Waiting Approval', 'active' => '', 'userGroupID' => 0];
}

foreach ($userGroups as $value) {
    $users_tabs[] = ['selector' => 'userGroupGrid' . $value['id'], 'queryString' => '?status=a&user_groups_id=' . $value['id'], 'icon' => 'fas fa-users', 'title' => $value['group_name'], 'active' => '', 'userGroupID' => $value['id']];
}
?>

<link href="<?php echo getURL('node_modules/flickity/dist/flickity.min.css'); ?>" rel="stylesheet" type="text/css" />
<div class="panel panel-default">
    <div class="panel-heading tabbable-line">
        <div class="btn-group pull-right">
            <button type="button" class="btn btn-default" id="addUserBtn">
                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <?php echo __("New User"); ?>
            </button>
            <a href="<?php echo $global['webSiteRootURL']; ?>usersGroups" class="btn btn-warning">
                <span class="fa fa-users"></span> <?php echo __("User Groups"); ?>
            </a>
            <a href="<?php echo $global['webSiteRootURL']; ?>mvideos" class="btn btn-success">
                <span class="fa fa-film"></span> <?php echo __("Videos"); ?>
            </a>
            <a href="<?php echo $global['webSiteRootURL']; ?>objects/getAllEmails.csv.php" class="btn btn-primary">
                <i class="fas fa-file-csv"></i> <?php echo __("CSV File"); ?>
            </a>
            <div class="btn btn-primary" data-toggle="tooltip" title="<?php echo __('Online users'); ?>">
                <i class="fas fa-users"></i> <span class="total_users_online">0</span>
            </div>
        </div>
        <div class="clearfix"></div>
        <?php
        $_REQUEST['rowCount'] = $_rowCount;
        $_REQUEST['current'] = $current;
        $items = array();
        foreach ($users_tabs as $value) {
            $label = "<i class=\"{$value['icon']}\"></i> " . __($value['title']);
            $items[] = array('href' => "#{$value['selector']}Tab", 'tooltip' => __($value['title']), 'onclick' => "startUserGrid('#{$value['selector']}', '{$value['queryString']}', " . intval($value['userGroupID']) . ");", 'label' => $label);
        }
        generateHorizontalFlickity($items);
        ?>
    </div>
    <div class="panel-body">
        <div class="tab-content">

            <?php
            foreach ($users_tabs as $value) {
            ?>
                <div id="<?php echo $value['selector']; ?>Tab" class="tab-pane fade in <?php echo $value['active']; ?>">
                    <?php
                    if (!empty($value['userGroupID'])) {
                    ?>
                        <div class="btn-group pull-left" id="filterButtonsUG<?php echo $value['userGroupID']; ?>">
                            <div class="btn-group ">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                    <span class="activeFilter"><?php echo __('All'); ?></span> <span class="caret"></span></button>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="#" id="filter<?php echo $value['userGroupID']; ?>_" onclick="userGroupFilter(<?php echo $value['userGroupID']; ?>, '');return false;"><?php echo __('All'); ?></a></li>
                                    <li><a href="#" id="filter<?php echo $value['userGroupID']; ?>_dynamic" onclick="userGroupFilter(<?php echo $value['userGroupID']; ?>, 'dynamic');return false;"><i class="fas fa-link"></i> <?php echo __('Dynamic User groups'); ?> (<?php echo __('Added by a plugin, PPV or Subscription'); ?>)</a></li>
                                    <li><a href="#" id="filter<?php echo $value['userGroupID']; ?>_permanent" onclick="userGroupFilter(<?php echo $value['userGroupID']; ?>, 'permanent');return false;;"><i class="fas fa-lock"></i> <?php echo __('Permanent User groups'); ?></a></li>
                                </ul>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                    <table id="<?php echo $value['selector']; ?>" class="table table-condensed table-hover table-striped">
                        <thead>
                            <tr>
                                <th data-column-id="id" data-width="80px"><?php echo __("#"); ?></th>
                                <th data-column-id="user" data-formatter="user"><?php echo __("User"); ?></th>
                                <th data-column-id="name" data-order="desc"><?php echo __("Name"); ?></th>
                                <th data-column-id="email"><?php echo __("E-mail"); ?></th>
                                <th data-column-id="phone"><?php echo __("Phone"); ?></th>
                                <th data-column-id="created"><?php echo __("Created"); ?></th>
                                <th data-column-id="modified"><?php echo __("Modified"); ?></th>
                                <th data-column-id="tags" data-formatter="tags" data-sortable="false"><?php echo __("Tags"); ?></th>
                                <th data-column-id="commands" data-formatter="commands" data-sortable="false" data-width="200px"></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
</div>


<div id="userFormModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo __("User Form"); ?></h4>
            </div>
            <div class="modal-body">
                <form class="form-compact" id="updateUserForm" onsubmit="">
                    <input type="hidden" id="inputUserId">
                    <div class="row">
                        <div class="col-sm-6">
                            <label for="inputUser"><?php echo __("User"); ?></label>
                            <input type="text" id="inputUser" class="form-control first" placeholder="<?php echo __("User"); ?>" autofocus required="required" data-toggle="tooltip" title="<?php echo __('User'); ?>">
                        </div>
                        <div class="col-sm-6">
                            <label for="inputPassword"><?php echo __("Password"); ?></label><?php
                                                                                            getInputPassword("inputPassword", 'class="form-control" required="required"  autocomplete="off"', __("Password"));
                                                                                            ?>
                        </div>
                        <div class="col-sm-6">
                            <label for="inputName">
                                <?php echo __("Name"); ?>
                            </label>
                            <input type="text" id="inputName" class="form-control " placeholder="<?php echo __("Name"); ?>" data-toggle="tooltip" title="<?php echo __('Name'); ?>">
                        </div>
                        <div class="col-sm-6">
                            <label for="inputEmail">
                                <?php echo __("E-mail"); ?>
                            </label>
                            <input type="email" id="inputEmail" class="form-control" placeholder="<?php echo __("E-mail"); ?>" data-toggle="tooltip" title="<?php echo __('E-mail'); ?>">
                        </div>
                        <div class="col-sm-6">
                            <label for="inputChannelName">
                                <?php echo __("Channel Name"); ?>
                            </label>
                            <input type="text" id="inputChannelName" class="form-control" placeholder="<?php echo __("Channel Name"); ?>" data-toggle="tooltip" title="<?php echo __('Channel Name'); ?>">
                        </div>
                        <div class="col-sm-6">
                            <label for="inputBirth">
                                <?php echo __("Birth"); ?>
                            </label>
                            <input type="date" id="inputBirth" class="form-control" placeholder="<?php echo __("Birth"); ?>" data-toggle="tooltip" title="<?php echo __('Birth'); ?>">
                        </div>
                        <div class="col-sm-6">
                            <label for="inputPhone">
                                <?php echo __("Phone"); ?>
                            </label>
                            <input type="text" id="inputPhone" class="form-control" placeholder="<?php echo __("Phone"); ?>" data-toggle="tooltip" title="<?php echo __('Phone'); ?>">
                        </div>
                        <div class="col-sm-6">
                            <label for="inputAnalyticsCode">
                                <?php echo __("Analytics Code"); ?>
                            </label>
                            <input type="text" id="inputAnalyticsCode" class="form-control last" placeholder="Google Analytics Code: UA-123456789-1" data-toggle="tooltip" title="<?php echo __('Analytics Code'); ?>">
                            <small>Do not paste the full javascript code, paste only the gtag id</small>
                        </div>
                    </div>
                    <?php
                    if (empty($advancedCustomUser->disableCompanySignUp) || !empty($advancedCustomUser->enableAffiliation)) {
                    ?>
                        <label for="is_company"><?php echo __("is a Company"); ?></label>
                        <select name="is_company" id="is_company" class="form-control last">
                            <?php
                            foreach (User::$is_company_status as $key => $value) {
                                if (!empty($advancedCustomUser->disableCompanySignUp) && $key == User::$is_company_status_WAITINGAPPROVAL) {
                                    continue;
                                }
                                echo "<option value='{$key}'>" . __($value) . "</option>";
                            }
                            ?>
                        </select>
                    <?php }
                    ?>

                    <ul class="list-group">
                        <li class="list-group-item <?php echo User::isAdmin() ? "" : "hidden"; ?>">
                            <?php echo __("is Admin"); ?>
                            <div class="material-switch pull-right">
                                <input type="checkbox" value="isAdmin" id="isAdmin" />
                                <label for="isAdmin" class="label-success"></label>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <?php echo __("Can Stream Videos"); ?>
                            <div class="material-switch pull-right">
                                <input type="checkbox" value="canStream" id="canStream" />
                                <label for="canStream" class="label-success"></label>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <?php echo __("Can Upload Videos"); ?>
                            <div class="material-switch pull-right">
                                <input type="checkbox" value="canUpload" id="canUpload" />
                                <label for="canUpload" class="label-success"></label>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <?php echo __("Can view chart"); ?>
                            <div class="material-switch pull-right">
                                <input type="checkbox" value="canViewChart" id="canViewChart" />
                                <label for="canViewChart" class="label-success"></label>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <?php echo __("Can create meet"); ?>
                            <div class="material-switch pull-right">
                                <input type="checkbox" value="canCreateMeet" id="canCreateMeet" />
                                <label for="canCreateMeet" class="label-success"></label>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <?php echo __("E-mail Verified"); ?>
                            <div class="material-switch pull-right">
                                <input type="checkbox" value="isEmailVerified" id="isEmailVerified" />
                                <label for="isEmailVerified" class="label-success"></label>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <?php echo __("is Active"); ?>
                            <div class="material-switch pull-right">
                                <input type="checkbox" value="status" id="status" />
                                <label for="status" class="label-success"></label>
                            </div>
                        </li>
                        <?php
                        print AVideoPlugin::getUserOptions();
                        ?>
                    </ul>
                    <ul class="list-group">
                        <li class="list-group-item active">
                            <?php echo __("User Groups"); ?>
                            <a href="#" class="btn btn-info btn-xs pull-right" data-toggle="popover" title="<?php echo __("What is User Groups"); ?>" data-placement="bottom" data-content="<?php echo __("By associating groups with this user, they will be able to see all the videos that are related to this group"); ?>"><span class="fa fa-question-circle" aria-hidden="true"></span> <?php echo __("Help"); ?></a>
                        </li>
                        <?php
                        foreach ($userGroups as $value) {
                        ?>
                            <li class="list-group-item usergroupsLi" id="usergroupsLi<?php echo $value['id']; ?>">
                                <span class="fa fa-unlock"></span>
                                <?php echo $value['group_name']; ?>
                                <span class="label label-info"><?php echo $value['total_videos']; ?> <?php echo __("Videos linked"); ?></span>
                                <span class="label label-warning dynamicLabel"><i class="fas fa-link"></i> <?php echo __("Dynamic group"); ?></span>
                                <div class="material-switch pull-right">
                                    <input id="userGroup<?php echo $value['id']; ?>" type="checkbox" value="<?php echo $value['id']; ?>" class="userGroups" />
                                    <label for="userGroup<?php echo $value['id']; ?>" class="label-warning"></label>
                                </div>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __("Close"); ?></button>
                <button type="button" class="btn btn-primary" id="saveUserBtn"><?php echo __("Save changes"); ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div id="userInfoModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo __("User Info"); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <label class="col-md-4 control-label"><?php echo __("First Name"); ?></label>
                    <div class="col-md-8 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                            <input id="first_name" class="form-control" type="text" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <label class="col-md-4 control-label"><?php echo __("Last Name"); ?></label>
                    <div class="col-md-8 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                            <input id="last_name" class="form-control" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <label class="col-md-4 control-label"><?php echo __("Address"); ?></label>
                    <div class="col-md-8 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                            <input id="address" class="form-control" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <label class="col-md-4 control-label"><?php echo __("Zip Code"); ?></label>
                    <div class="col-md-8 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                            <input id="zip_code" class="form-control" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <label class="col-md-4 control-label"><?php echo __("Country"); ?></label>
                    <div class="col-md-8 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                            <input id="country" class="form-control" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-4 control-label"><?php echo __("Region"); ?></label>
                    <div class="col-md-8 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                            <input id="region" class="form-control" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-4 control-label"><?php echo __("City"); ?></label>
                    <div class="col-md-8 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                            <input id="city" class="form-control" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <label class="col-md-4 control-label"><?php echo __("Document"); ?></label>
                    <div class="col-md-8 inputGroupContainer">
                        <div class="input-group">
                            <img src="" class="img img-responsive img-thumbnail" id="documentImage" />
                        </div>
                    </div>
                </div>

            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script src="<?php echo getURL('node_modules/flickity/dist/flickity.pkgd.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('node_modules/flickity-bg-lazyload/bg-lazyload.js'); ?>" type="text/javascript"></script>

<script>
    function isAnalytics() {
        str = $('#inputAnalyticsCode').val();
        return true;
        //return str === '' || (/^ua-\d{4,9}-\d{1,4}$/i).test(str.toString());
    }

    $(document).ready(function() {

        startUserGrid("#<?php echo $users_tabs[0]['selector']; ?>", "<?php echo $users_tabs[0]['queryString']; ?>", <?php echo intval($users_tabs[0]['userGroupID']); ?>);
        $('#addUserBtn').click(function(evt) {
            $('#inputUserId').val('');
            $('#inputUser').val('');
            $('#inputPassword').val('');
            $('#inputEmail').val('');
            $('#inputName').val('');
            $('#inputChannelName').val('');
            $('#inputPhone').val('');
            $('#inputBirth').val('');
            $('#inputAnalyticsCode').val('');
            $('#is_company').val(0);
            $('#isAdmin').prop('checked', false);
            $('#canStream').prop('checked', false);
            $('#canUpload').prop('checked', false);
            $('#canViewChart').prop('checked', false);
            $('#canCreateMeet').prop('checked', false);
            $('.userGroups').prop('checked', false);
            $('#status').prop('checked', true);
            $('#isEmailVerified').prop('checked', false);
            <?php
            print AVideoPlugin::addUserBtnJS();
            ?>
            $('#userFormModal').modal();
        });
        $('#saveUserBtn').click(function(evt) {
            $('#updateUserForm').submit();
        });
        $('#updateUserForm').submit(function(evt) {
            evt.preventDefault();
            if (!isAnalytics()) {
                avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("Your analytics code is wrong"); ?>", "error");
                $('#inputAnalyticsCode').focus();
                return false;
            }

            modal.showPleaseWait();
            var selectedUserGroups = [];
            $('.userGroups:checked').each(function() {
                selectedUserGroups.push($(this).val());
            });
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>objects/userAddNew.json.php',
                data: {
                    <?php
                    print AVideoPlugin::updateUserFormJS();
                    ?> "id": $('#inputUserId').val(),
                    "user": $('#inputUser').val(),
                    "pass": $('#inputPassword').val(),
                    "email": $('#inputEmail').val(),
                    "name": $('#inputName').val(),
                    "phone": $('#inputPhone').val(),
                    "birth": $('#inputBirth').val(),
                    "channelName": $('#inputChannelName').val(),
                    "analyticsCode": $('#inputAnalyticsCode').val(),
                    "isAdmin": $('#isAdmin').is(':checked'),
                    "canStream": $('#canStream').is(':checked'),
                    "is_company": $('#is_company').val(),
                    "canUpload": $('#canUpload').is(':checked'),
                    "canViewChart": $('#canViewChart').is(':checked'),
                    "canCreateMeet": $('#canCreateMeet').is(':checked'),
                    "status": $('#status').is(':checked') ? 'a' : 'i',
                    "isEmailVerified": $('#isEmailVerified').is(':checked'),
                    "userGroups": selectedUserGroups,
                    "do_not_login": 1,
                    "securityToken": '<?php echo getToken(3600); ?>'
                },
                type: 'post',
                success: function(response) {
                    if (!response.error) {
                        $('#userFormModal').modal('hide');
                        $('.bootgrid-table').bootgrid("reload");
                    }
                    avideoResponse(response);
                    console.log('user save', response);
                    modal.hidePleaseWait();
                }
            });
            return false;
        });
    });

    var userGroupShowOnly = '';
    var userGroupQueryString = '';

    function userGroupFilter(user_groups_id, value) {
        console.log('Filter usergroup', user_groups_id, value);
        userGroupShowOnly = value;
        $('#userGroupTab' + user_groups_id + ' .activeFilter').html($('#filter' + user_groups_id + '_' + value).html());
        $('.tooltip').tooltip('hide');
        var selector = '#userGroupGrid' + user_groups_id;
        if ($(selector).hasClass('bootgrid-table')) {
            $(selector).bootgrid('reload');
        }
    }

    function getUserGridURL() {
        var url = webSiteRootURL + "objects/users.json.php" + userGroupQueryString;
        url = addGetParam(url, 'userGroupShowOnly', userGroupShowOnly);
        return url;
    }

    function startUserGrid(selector, queryString, user_groups_id) {
        userGroupQueryString = queryString;
        if (user_groups_id) {
            userGroupFilter(user_groups_id, '');
        }
        if ($(selector).hasClass('bootgrid-table')) {
            console.log(selector, 'already loaded');
            return false;
        }
        var grid = $(selector).bootgrid({
            labels: {
                noResults: "<?php echo __("No results found!"); ?>",
                all: "<?php echo __("All"); ?>",
                infos: "<?php echo __("Showing {{ctx.start}} to {{ctx.end}} of {{ctx.total}} entries"); ?>",
                loading: "<?php echo __("Loading..."); ?>",
                refresh: "<?php echo __("Refresh"); ?>",
                search: "<?php echo __("Search"); ?>",
            },
            ajax: true,
            url: getUserGridURL,
            formatters: {
                "commands": function(column, row) {
                    var editBtn = '<button type="button" class="btn btn-xs btn-default command-edit" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="<?php echo __('Edit'); ?>"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>'
                    var infoBtn = '<button type="button" class="btn btn-xs btn-default command-info" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="<?php echo __('Info'); ?>"><i class="fas fa-info-circle"></i></button>'
                    var liveNowBtn = '<button type="button" class="btn btn-default btn-xs command-copy-livenow" data-row-id="' + row.id + '" data-toggle="tooltip" title="<?php echo __("Copy Live Now URL"); ?>"><i class="fa-regular fa-copy"></i></button>';

                    //var deleteBtn = '<button type="button" class="btn btn-default btn-xs command-delete"  data-row-id="' + row.id + '  data-toggle="tooltip" data-placement="left" title="Delete""><span class="glyphicon glyphicon-erase" aria-hidden="true"></span></button>';
                    var pluginsButtons = '<br><?php echo AVideoPlugin::getUsersManagerListButton(); ?>';
                    return editBtn + infoBtn + liveNowBtn + pluginsButtons;
                },
                "tags": function(column, row) {
                    var tags = '';
                    for (var i in row.tags) {
                        if (typeof row.tags[i].type == "undefined") {
                            continue;
                        }
                        tags += "<span class=\"label label-" + row.tags[i].type + " fix-width\">" + row.tags[i].text + "</span><br>";
                    }
                    return tags;
                },
                "user": function(column, row) {
                    var photo = '';
                    if (row.photoURL) {
                        photo = "<br><img src='" + row.photo + "' class='img img-responsive img-rounded img-thumbnail' style='max-width:100px;'/>";
                    }
                    return row.user + photo;
                }
            }
        }).on("loaded.rs.jquery.bootgrid", function() {
            /* Executes after data is loaded and rendered */
            grid.find(".command-edit").on("click", function(e) {
                var row_index = $(this).closest('tr').index();
                var row = $(selector).bootgrid("getCurrentRows")[row_index];
                console.log(row);
                $('#inputUserId').val(row.id);
                $('#inputUser').val(row.user);
                $('#inputPassword').val('');
                $('#inputEmail').val(row.email);
                $('#inputName').val(row.name);
                $('#inputChannelName').val(row.channelName);
                $('#inputPhone').val(row.phone);
                $('#inputBirth').val(row.birth_date);
                $('#inputAnalyticsCode').val(row.analyticsCode);
                $('.userGroups').prop('checked', false);
                $('.usergroupsLi').removeClass('dynamic');
                $('.usergroupsLi input').removeAttr('disabled');
                $('#is_company').val(row.is_company);

                for (var index in row.groups) {
                    $('#userGroup' + row.groups[index].id).prop('checked', true);
                    if (row.groups[index].isDynamic) {
                        $('#usergroupsLi' + row.groups[index].id).addClass('dynamic');
                        $('#usergroupsLi' + row.groups[index].id + ' input').attr("disabled", true);
                    }
                }
                $('#isAdmin').prop('checked', (row.isAdmin == "1" ? true : false));
                $('#canStream').prop('checked', (row.canStream == "1" ? true : false));
                $('#canUpload').prop('checked', (row.canUpload == "1" ? true : false));
                $('#canViewChart').prop('checked', (row.canViewChart == "1" ? true : false));
                $('#canCreateMeet').prop('checked', (row.canCreateMeet == "1" ? true : false));
                $('#status').prop('checked', (row.status === "a" ? true : false));
                $('#isEmailVerified').prop('checked', (row.isEmailVerified == "1" ? true : false));
                <?php
                print AVideoPlugin::loadUsersFormJS();
                ?>

                $('#userFormModal').modal();
            }).end().find(".command-info").on("click", function(e) {

                var row_index = $(this).closest('tr').index();
                var row = $(selector).bootgrid("getCurrentRows")[row_index];
                console.log(row);
                modal.showPleaseWait();
                $('#first_name').val(row.first_name);
                $('#last_name').val(row.last_name);
                $('#address').val(row.address);
                $('#zip_code').val(row.zip_code);
                $('#country').val(row.country);
                $('#region').val(row.region);
                $('#city').val(row.city);
                $('#documentImage').attr('src', '<?php echo $global['webSiteRootURL']; ?>objects/userDocument.png.php?users_id=' + row.id);
                $('#userInfoModal').modal();
                modal.hidePleaseWait();
            }).end().find(".command-copy-livenow").on("click", function(e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                var text = webSiteRootURL + 'channel/' + row.channelName + '/liveNow?muted=1';
                console.log(text);
                copyToClipboard(text);
            });
        });
    }
</script>