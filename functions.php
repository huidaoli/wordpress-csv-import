<?php

/*------------ 将csv import 链接添加到 member post type Start ------------*/

function csv_import(){
  add_submenu_page( 'edit.php?post_type=member', 'CSV import', 'CSV import', 'manage_options', 'theme-csv-import', 'theme_csv_func');
}
add_action('admin_menu', 'csv_import');

/*------------ 将csv import 链接添加到 member post type End --------------*/

/*------------ 点击后 csv import后执行 theme_csv_func 函数 Start ------------*/

function theme_csv_func(){
    // 获取现在在第几步
    if (empty ($_GET['step'])){
        $step = 0;
    } else {
        $step = (int) $_GET['step'];
    }
    switch ($step) {
        // 如果为0显示第一步界面
        case 0:
            ?>
            <div class="wrap">
                <h2>CSV import step 1</h2></div>
                <p>Choose a CSV (.csv) file to upload, then click Upload file and import.</p>
                <p><a href="<?php bloginfo('template_url');?>/players.csv" target="_blank">Click here</a> to download a sample copy of the CSV file.</p>
            <?php
            // 添加上传表单，并链接到第二步
            wp_import_upload_form( add_query_arg('step', 1) );
            break;
        case 1:
            // 接收第一步上传的文件
            $file = wp_import_handle_upload();
            // 验证文件
            if ( isset( $file['error'] ) ) {
                echo '<p><strong>Sorry, there has been an error.</strong><br />';
                echo esc_html( $file['error'] ) . '</p>';
                return false;
            } else if ( ! file_exists( $file['file'] ) ) {
                echo '<p><strong>Sorry, there has been an error.</strong><br />';
                printf( __( 'The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'AAA' ), esc_html( $file['file'] ) );
                echo '</p>';
                return false;
            }
            $fileid = (int) $file['id'];
            $attached_file = get_attached_file($fileid);
            ?>
            <div class="wrap">
                <div id="icon-options-general" class="icon32"><br></div>
                <h2>CSV import step 2</h2></div>
            <?php
            $file = fopen($attached_file,'r');
            // 简单验证一下是否和制作和CSV相符，避免用户上传错文件
            if (count(fgetcsv($file))==13) {
                $line_number = 0;
                while ($data = fgetcsv($file)) {
                    // 遍历CSV
                    // 拼接标题为 firstname + midname + lastname
                    $customtitle = $data[0].' '.$data[1].' '.$data[2];
                    // 判断文章是否存在
                    $have = post_exists($customtitle);
                    // 用户CSV中生日格式不标准，字符串处理
                    list($d,$m,$y) = explode('/',trim((string)$data[5]));
                    if (strlen($y) == 2) {
                        if ($y <= 99 && $y > 40) {
                            $prefix = 19;
                        } else {
                            $prefix = 20;
                        }
                    }
                    if (strlen($d) == 1) {
                        $d = '0'.$d;
                    }
                    if (strlen($m) == 1) {
                        $m = '0'.$m;
                    }
                    $dob = "$d/$m/$prefix$y";
                    // 如果没有记录
                    if ($have == 0) {
                        // 创建post
                        $my_post = array(

                            'post_title' => $customtitle,
                            'post_content' => '',
                            'post_author' => 1,
                            'post_status' => 'publish',
                            'post_type' => 'member'
                        );
                        $post_id = wp_insert_post( $my_post );
                        // 创建完再更新字段
                        update_field('first_name', trim((string)$data[0]) , $post_id);
                        update_field('middle_name', trim((string)$data[1]) , $post_id);
                        update_field('last_name', trim((string)$data[2]) , $post_id);
                        update_field('ffa', trim((string)$data[3]) , $post_id);
                        update_field('gufc', trim((string)$data[4]) , $post_id);
                        update_field('dob', $dob, $post_id);
                        update_field('genner', trim((string)$data[6]) , $post_id);
                        update_field('photo_url', trim((string)$data[7]) , $post_id);
                        update_field('regisitration_history', trim((string)$data[8]) , $post_id);
                        update_field('team_name', trim((string)$data[9]) , $post_id);
                        update_field('team_age_group', trim((string)$data[10]) , $post_id);
                        update_field('team_division', trim((string)$data[11]) , $post_id);
                        update_field('email', trim((string)$data[12]) , $post_id);
                        // 这2条客户在CSV中删掉了所以给他清除掉数据
                        update_field('coach', '' , $post_id);
                        update_field('manager', '' , $post_id);
                        echo "<p>Player $customtitle has been created.</p>";
                    } else {
                        // 直接更新字段
                        update_field('first_name', trim((string)$data[0]) , $have);
                        update_field('middle_name', trim((string)$data[1]) , $have);
                        update_field('last_name', trim((string)$data[2]) , $have);
                        update_field('ffa', trim((string)$data[3]) , $have);
                        update_field('gufc', trim((string)$data[4]) , $have);
                        update_field('dob', $dob , $have);
                        update_field('genner', trim((string)$data[6]) , $have);
                        update_field('photo_url', trim((string)$data[7]) , $have);
                        update_field('regisitration_history', trim((string)$data[8]) , $have);
                        update_field('team_name', trim((string)$data[9]) , $have);
                        update_field('team_age_group', trim((string)$data[10]) , $have);
                        update_field('team_division', trim((string)$data[11]) , $have);
                        update_field('email', trim((string)$data[12]) , $have);
                        update_field('coach', '' , $have);
                        update_field('manager', '' , $have);
                        echo "<p>Player $customtitle has been updated.</p>";
                    }
                }
            } else {
                echo '<p><strong>Sorry, This is not an effective file.</strong><br /></p>';
            }
            fclose($file);
        default:
            # code...
            break;
    }
}

/*------------ 点击后 csv import后执行 theme_csv_func 函数 End --------------*/



?>