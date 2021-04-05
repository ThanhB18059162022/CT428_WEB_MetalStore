<!-- Thêm header -->
<?php $title="Quản lý hàng hóa"; 
    require_once "../header-ad.php";
?>
<h1>Quản lý hàng hóa</h1>

<div class="btn-crt">
    <a href="create.php">
        <button class="btn"><img class="ico" src="<?php echo $_PATH["img"]; ?>/plus-ico.png"></button>
    </a>

</div>
<table class="content-table">
    <thead>
        <tr>
            <th>Mã số</th>
            <th>Loại hàng hóa</th>
            <th>Tên hàng hóa</th>
            <th>Chỉnh sửa</th>
        </tr>
    </thead>
    <tbody>
    <?php 
        include_once $_PATH["dao"];
        $img = $_PATH["img"];
        $icon = "<img src=\"$img/edit-ico.png\" class=\"ico\">";
        foreach ($items as $key => $value) {
            echo sprintf(
                "  
                    <tr onclick=\"window.location='detail.php?id=%s';\">
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                        <td><a href=\"edit.php?id=%s\">$icon</a></td>
                    </tr>
                ",
            $value["MSHH"],
            $value["MSHH"],
            $value["TenLoai"],
            $value["TenHH"],
            $value["MSHH"]
            );
        }
    ?>
     </tbody>
</table>
<!-- Thêm footer -->
<?php 
    require_once $_PATH["footer"];
?>