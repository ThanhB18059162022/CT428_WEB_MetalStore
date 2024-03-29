<?php
    $title = "Thông tin hàng hóa";
    $path = $_SERVER['DOCUMENT_ROOT']."/CT428_WEB";
    require_once "$path/layouts/header-ad.php";
    require_once $_PATH["dao"];
    $dao = new ItemDAO();
    
    // Lấy ra hàng hóa theo id
    $id = $_GET["id"];
    $item = $dao->getItemById($id);

    // Nếu không tồn tại
    if(!isset($item))
    {
        $err = $_PATH["err"];
        header("Location: $err", true);
    }

    // Lấy ra hình
    $img = $_PATH["img"].$item["MSHH"]."/".$item["Location"];
?>

<h1>Thông tin chi tiết hàng hóa</h1>
<h2 class="hdr" style="color:blue">Tên hàng hóa: <?php echo $item["TenHH"];?> </h2>
<hr>
    <div class="dtl">
        <img class=img-dis src="<?php echo $img;?>">
        <div>
            <div>Tên hàng hóa: <?php echo $item["TenHH"]; ?></div>
            <div>Tên loại: <?php echo $item["TenLoai"]; ?></div>
            <div>Quy cách: <?php 
                $qc = explode("-",$item["QuyCach"]);
                echo $qc[0]." mét - ".$qc[1]." mét<sub>/kg</sub>";
            ?></div>           
            <div>Giá: <?php echo $item["Gia"]." vnd"; ?></div>
            <div>Số lượng: <?php echo $item["SoLuongHang"]; ?></div> 
        </div>
    </div>
    
<div class="lnk">
    <a href="edit.php?id=<?php echo $item["MSHH"];?>"><h3>Chỉnh sửa thông tin</h3></a>
    <a href="manage.php"><h3>Về trang quản lý</h3></a>
</div>

<?php
    require_once $_PATH["footer"];
?>