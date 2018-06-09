<?php

//--- FILEMANAGER MAIN
$fm->show_header(); // HEADER
$fm->show_nav_path(FM_PATH); // current path

// messages
$fm->show_message();

$num_files = count($files);
$num_folders = count($folders);
$all_files_size = 0;
?>
<form action="" method="post">
<input type="hidden" name="p" value="<?php echo $fm->enc(FM_PATH) ?>">
<input type="hidden" name="group" value="1">
<table class="table"><thead><tr>
<?php if (!FM_READONLY): ?><th style="width:3%"><label><input type="checkbox" title="<?php echo $fm->t('Invert selection', $lang) ?>" onclick="checkbox_toggle()"></label></th><?php endif; ?>
<th><?php echo $fm->t('Name', $lang) ?></th><th style="width:10%"><?php echo $fm->t('Size', $lang) ?></th>
<th style="width:12%"><?php echo $fm->t('Modified', $lang) ?></th>
<?php if (!FM_IS_WIN): ?><th style="width:6%"><?php echo $fm->t('Perms', $lang) ?></th><th style="width:10%"><?php echo $fm->t('Owner', $lang) ?></th><?php endif; ?>
<th style="width:<?php if (!FM_READONLY): ?>13<?php else: ?>6.5<?php endif; ?>%"><?php echo $fm->t('Actions', $lang) ?></th></tr></thead>
<?php
// link to parent folder
if ($parent !== false) {
    ?>
<tr><?php if (!FM_READONLY): ?><td></td><?php endif; ?><td colspan="<?php echo !FM_IS_WIN ? '6' : '4' ?>"><a href="<?php echo $fm->base_query; echo urlencode($parent) ?>"><i class="fa fa-chevron-circle-left"></i> ..</a></td></tr>
<?php
}
foreach ($folders as $f) {
    $is_link = is_link($path . '/' . $f);
    $img = $is_link ? 'icon-link_folder' : 'icon-folder';
    $modif = date(FM_DATETIME_FORMAT, filemtime($path . '/' . $f));
    $perms = substr(decoct(fileperms($path . '/' . $f)), -4);
    if (function_exists('posix_getpwuid') && function_exists('posix_getgrgid')) {
        $owner = posix_getpwuid(fileowner($path . '/' . $f));
        $group = posix_getgrgid(filegroup($path . '/' . $f));
    } else {
        $owner = array('name' => '?');
        $group = array('name' => '?');
    }
    ?>
<tr>
<?php if (!FM_READONLY): ?><td><label><input type="checkbox" name="file[]" value="<?php echo $fm->enc($f) ?>"></label></td><?php endif; ?>
<td><div class="filename"><a href="<?php echo $fm->base_query; echo urlencode(trim(FM_PATH . '/' . $f, '/')) ?>"><i class="<?php echo $img ?>"></i> <?php echo $fm->convert_win($f) ?></a><?php echo ($is_link ? ' &rarr; <i>' . readlink($path . '/' . $f) . '</i>' : '') ?></div></td>
<td><?php echo $fm->t('Folder', $lang) ?></td><td><?php echo $modif ?></td>
<?php if (!FM_IS_WIN): ?>
<td><?php if (!FM_READONLY): ?><a title="<?php echo $fm->t('Change Permissions', $lang) ?>" href="<?php echo $fm->base_query; echo urlencode(FM_PATH) ?>&amp;chmod=<?php echo urlencode($f) ?>"><?php echo $perms ?></a><?php else: ?><?php echo $perms ?><?php endif; ?></td>
<td><?php echo $owner['name'] . ':' . $group['name'] ?></td>
<?php endif; ?>
<td class="inline-actions"><?php if (!FM_READONLY): ?>
<a title="<?php echo $fm->t('Delete', $lang) ?>" href="<?php echo $fm->base_query; echo urlencode(FM_PATH) ?>&amp;del=<?php echo urlencode($f) ?>" onclick="return confirm('<?php echo $fm->t('Delete folder?') ?>');"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
<a title="<?php echo $fm->t('Rename', $lang) ?>" href="#" onclick="rename('<?php echo $fm->enc(FM_PATH) ?>', '<?php echo $fm->enc($f) ?>');return false;"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
<a title="<?php echo $fm->t('Copy to...') ?>" href="<?php echo $fm->base_query; ?>&amp;copy=<?php echo urlencode(trim(FM_PATH . '/' . $f, '/')) ?>"><i class="fa fa-files-o" aria-hidden="true"></i></a>
<?php endif; ?>
<a title="<?php echo $fm->t('Direct link', $lang) ?>" href="<?php echo FM_ROOT_URL . (FM_PATH != '' ? '/' . FM_PATH : '') . '/' . $f . '/' ?>" target="_blank"><i class="fa fa-link" aria-hidden="true"></i></a>
</td></tr>
    <?php
    flush();
}

foreach ($files as $f) {
    $is_link = is_link($path . '/' . $f);
    $img = $is_link ? 'fa fa-file-text-o' : $fm->get_file_icon_class($path . '/' . $f);
    $modif = date("d.m.y H:i", filemtime($path . '/' . $f));
    $filesize_raw = filesize($path . '/' . $f);
    $filesize = $fm->get_filesize($filesize_raw);
    $filelink = $fm->base_query . urlencode(FM_PATH) . '&amp;view=' . urlencode($f);
    $all_files_size += $filesize_raw;
    $perms = substr(decoct(fileperms($path . '/' . $f)), -4);
    if (function_exists('posix_getpwuid') && function_exists('posix_getgrgid')) {
        $owner = posix_getpwuid(fileowner($path . '/' . $f));
        $group = posix_getgrgid(filegroup($path . '/' . $f));
    } else {
        $owner = array('name' => '?');
        $group = array('name' => '?');
    }
    ?>
<tr>
<?php if (!FM_READONLY): ?><td><label><input type="checkbox" name="file[]" value="<?php echo $fm->enc($f) ?>"></label></td><?php endif; ?>
<td><div class="filename"><a href="<?php echo $filelink ?>" title="<?php echo $fm->t('File info', $lang) ?>"><i class="<?php echo $img ?>"></i> <?php echo $fm->convert_win($f) ?></a><?php echo ($is_link ? ' &rarr; <i>' . readlink($path . '/' . $f) . '</i>' : '') ?></div></td>
<td><span title="<?php printf($fm->t('%s bytes'), $filesize_raw) ?>"><?php echo $filesize ?></span></td>
<td><?php echo $modif ?></td>
<?php if (!FM_IS_WIN): ?>
<td><?php if (!FM_READONLY): ?><a title="<?php echo $fm->t('Change Permissions', $lang) ?>" href="<?php echo $fm->base_query; echo urlencode(FM_PATH) ?>&amp;chmod=<?php echo urlencode($f) ?>"><?php echo $perms ?></a><?php else: ?><?php echo $perms ?><?php endif; ?></td>
<td><?php echo $owner['name'] . ':' . $group['name'] ?></td>
<?php endif; ?>
<td class="inline-actions">
<?php if (!FM_READONLY): ?>
<a title="<?php echo $fm->t('Delete', $lang) ?>" href="<?php echo $fm->base_query; echo urlencode(FM_PATH) ?>&amp;del=<?php echo urlencode($f) ?>" onclick="return confirm('<?php echo $fm->t('Delete file?') ?>');"><i class="fa fa-trash-o"></i></a>
<a title="<?php echo $fm->t('Rename', $lang) ?>" href="#" onclick="rename('<?php echo $fm->enc(FM_PATH) ?>', '<?php echo $fm->enc($f) ?>');return false;"><i class="fa fa-pencil-square-o"></i></a>
<a title="<?php echo $fm->t('Copy to...') ?>" href="<?php echo $fm->base_query; echo urlencode(FM_PATH) ?>&amp;copy=<?php echo urlencode(trim(FM_PATH . '/' . $f, '/')) ?>"><i class="fa fa-files-o"></i></a>
<?php endif; ?>
<a title="<?php echo $fm->t('Direct link', $lang) ?>" href="<?php echo FM_ROOT_URL . (FM_PATH != '' ? '/' . FM_PATH : '') . '/' . $f ?>" target="_blank"><i class="fa fa-link"></i></a>
<a title="<?php echo $fm->t('Download', $lang) ?>" href="<?php echo $fm->base_query; echo urlencode(FM_PATH) ?>&amp;dl=<?php echo urlencode($f) ?>"><i class="fa fa-download"></i></a>
</td></tr>
    <?php
    flush();
}

if (empty($folders) && empty($files)) {
    ?>
<tr><?php if (!FM_READONLY): ?><td></td><?php endif; ?><td colspan="<?php echo !FM_IS_WIN ? '6' : '4' ?>"><em><?php echo $fm->t('Folder is empty', $lang) ?></em></td></tr>
<?php
} else {
    ?>
<tr><?php if (!FM_READONLY): ?><td class="gray"></td><?php endif; ?><td class="gray" colspan="<?php echo !FM_IS_WIN ? '6' : '4' ?>">
<?php echo $fm->t('Full size:', $lang) ?> <span title="<?php printf($fm->t('%s bytes'), $all_files_size) ?>"><?php echo $fm->get_filesize($all_files_size) ?></span>,
<?php echo $fm->t('files:', $lang) ?> <?php echo $num_files ?>,
<?php echo $fm->t('folders:', $lang) ?> <?php echo $num_folders ?>
</td></tr>
<?php
}
?>
</table>
<?php if (!FM_READONLY): ?>
<p class="path footer-links"><a href="#/select-all" class="group-btn" onclick="select_all();return false;"><i class="fa fa-check-square"></i> <?php echo $fm->t('Select all', $lang) ?></a> &nbsp;
<a href="#/unselect-all" class="group-btn" onclick="unselect_all();return false;"><i class="fa fa-window-close"></i> <?php echo $fm->t('Unselect all', $lang) ?></a> &nbsp;
<a href="#/invert-all" class="group-btn" onclick="invert_all();return false;"><i class="fa fa-th-list"></i> <?php echo $fm->t('Invert selection', $lang) ?></a> &nbsp;
<input type="submit" class="hidden" name="delete" id="a-delete" value="<?php echo $fm->t('Delete', $lang) ?>" onclick="return confirm('<?php echo $fm->t('Delete selected files and folders?') ?>')">
<a href="javascript:document.getElementById('a-delete').click();" class="group-btn"><i class="fa fa-trash"></i> <?php echo $fm->t('Delete', $lang) ?> </a> &nbsp;
<input type="submit" class="hidden" name="zip" id="a-zip" value="<?php echo $fm->t('Zip', $lang) ?>" onclick="return confirm('<?php echo $fm->t('Create archive?') ?>')">
<a href="javascript:document.getElementById('a-zip').click();" class="group-btn"><i class="fa fa-file-archive-o"></i> <?php echo $fm->t('Zip', $lang) ?> </a> &nbsp;
<input type="submit" class="hidden" name="copy" id="a-copy" value="<?php echo $fm->t('Copy', $lang) ?>">
<a href="javascript:document.getElementById('a-copy').click();" class="group-btn"><i class="fa fa-files-o"></i> <?php echo $fm->t('Copy', $lang) ?> </a>
<?php endif; ?>
</form>

<?php
$fm->show_footer();
