<?php
abstract class DAO
{
    public $servername;
    public $database;
    public $username;
    public $password;

    // Create connection
    public $cnn;

    public function __construct($servername = "localhost", $username = "root", $password = "", $database = "quanlydathang")
    {
        $this->servername = $servername;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;

        $this->cnn = new mysqli($this->servername, $this->username, $this->password, $this->database);
        // Check connection
        if ($this->cnn->connect_error) {
            die("Connection failed: " . $this->cnn->connect_error);
        }
    }
}

/* #region Emp DAO */
class EmpDAO extends DAO
{
    public function getEmpByMSNV($msnv)
    {
        $sql = "SELECT * FROM NhanVien WHERE msnv = ?";

        $stmt = $this->cnn->prepare($sql);

        $stmt->bind_param("s", $msnv);


        if ($stmt->execute()) {
            $emp = $stmt->get_result()->fetch_assoc();
        }

        return $emp;
    }
}
/* #endregion */

/* #region  Item DAO */
class ItemDAO extends DAO
{
    // Lấy ra danh sách hàng hóa
    public function getAllItem()
    {
        $sql = "SELECT hh.*, lhh.TenLoaiHangHoa TenLoai FROM HangHoa hh, LoaiHangHoa lhh
                WHERE hh.MaLoaiHang = lhh.MaLoaiHangHoa;";
        $stmt = $this->cnn->query($sql);

        $items = [];

        while ($r = $stmt->fetch_assoc()) {
            $items[] = $r;
        }

        return $items;
    }

    // Lấy ra loại hàng
    public function getAllItemType()
    {
        $sql = "SELECT * FROM LoaiHangHoa;";
        $stmt = $this->cnn->query($sql);

        $items = [];

        while ($r = $stmt->fetch_assoc()) {
            $items[] = $r;
        }

        return $items;
    }

    // Lấy ra hàng hóa theo id
    public function getItemById($id)
    {
        $sql = "SELECT hh.*, lhh.TenLoaiHangHoa TenLoai FROM HangHoa hh, LoaiHangHoa lhh
                WHERE hh.MaLoaiHang = lhh.MaLoaiHangHoa AND hh.MSHH = ?;";

        $stmt = $this->cnn->prepare($sql);

        $stmt->bind_param("i", $id);


        if ($stmt->execute()) {
            $item = $stmt->get_result()->fetch_assoc();
        }

        return $item;
    }

    // Cập nhật hàng hóa
    public function updateItem($item)
    {
        $sql = "UPDATE HangHoa
                    SET TenHH = ?,
                    MaLoaiHang = ?,
                    QuyCach = ?,
                    Gia = ?,
                    SoLuongHang = ?,
                    Location = ?,
                    GhiChu = ?
                WHERE MSHH = ?
        ;";

        $stmt = $this->cnn->prepare($sql);

        $stmt->bind_param(
            "sisdissi",
            $item["TenHH"],
            $item["MaLoaiHang"],
            $item["QuyCach"],
            $item["Gia"],
            $item["SoLuongHang"],
            $item["Location"],
            $item["GhiChu"],
            $item["MSHH"]
        );

        return $stmt->execute();
    }

    // Thêm hàng hóa
    public function addItem($item)
    {
        $sql = "INSERT INTO HangHoa(TenHH, QuyCach, Gia, SoLuongHang, MaLoaiHang, Location, GhiChu) 
                VALUES(?, ?, ?, ?, ?, ?, ?);";

        $stmt = $this->cnn->prepare($sql);

        $stmt->bind_param(
            "ssdiiss",
            $item["TenHH"],
            $item["QuyCach"],
            $item["Gia"],
            $item["SoLuongHang"],
            $item["MaLoaiHang"],
            $item["Location"],
            $item["GhiChu"]
        );

        return $stmt->execute();
    }

    // Xóa hàng hóa 
    public function deleteItem($id)
    {
        $sql = "DELETE FROM HangHoa WHERE MSHH = ?;";

        $stmt = $this->cnn->prepare($sql);

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}
/* #endregion */

/* #region  Đặt hàng */
class DatHangDAO extends DAO
{
    public function getDSDatHang()
    {
        $sql = "SELECT dt.SoDonDH, kh.HoTenKH, kh.Email, kh.SoDienThoai, ct.GiaDatHang, ct.GiamGia, hh.TenHH FROM HangHoa as hh, ChiTietDathang as ct, Dathang as dt, KhachHang as kh
                WHERE hh.MSHH = ct.MSHH AND ct.SoDonDH = dt.SoDonDH AND dt.MSKH = kh.MSKH
                ORDER BY dt.NgayDH";
        $stmt = $this->cnn->query($sql);

        $items = [];

        if($stmt !== false){
            while ($r = $stmt->fetch_assoc()) {
                $items[] = $r;
            }
        }

        return $items;
    }

    public function getHD($SoDonDH)
    {
        $sql = "SELECT * FROM LoaiHangHoa as l, HangHoa as hh, ChiTietDathang as ct, Dathang as dt, KhachHang as kh, DiaChiKH as dc, NhanVien as nv
                WHERE l.MaLoaiHangHoa = hh.MaLoaiHang AND hh.MSHH = ct.MSHH AND ct.SoDonDH = dt.SoDonDH AND dt.MSKH = kh.MSKH AND dt.msnv = nv.msnv
                AND kh.MSKH = dc.MSKH AND dt.SoDonDH = ?;";
        $stmt = $this->cnn->prepare($sql);

        $stmt->bind_param("i", $SoDonDH);


        if ($stmt->execute()) {
            $item = $stmt->get_result()->fetch_assoc();
        }

        return $item;
    }

    public function delDH($SoDonDH)
    {
        $sql = "CALL spHuyDatHang(?);";
        $stmt = $this->cnn->prepare($sql);

        $stmt->bind_param("i", $SoDonDH);


        return $stmt->execute();
    }

    public function creHD($MSKH, $MSNV, $NgayGH, $MSHH, $SoLuongHang, $Gia, $GiamGia)
    {
        $sql = "CALL spDatHang(?, ?, ?, ?, ?, ?, ?);";
        $stmt = $this->cnn->prepare($sql);

        $stmt->bind_param("issiidd", $MSKH, $MSNV, $NgayGH, $MSHH, $SoLuongHang, $Gia, $GiamGia);

        return $stmt->execute();
    }
}
/* #endregion */

/* #region  Khách hàng */
class KhacHangDAO extends DAO
{
    public function getDSKhachHang()
    {
        $sql = "SELECT * FROM KhachHang kh, DiaChiKH dc WHERE kh.MSKH = dc.MSKH;";
        $stmt = $this->cnn->query($sql);

        $cstmrs = [];

        while ($r = $stmt->fetch_assoc()) {
            $cstmrs[] = $r;
        }

        return $cstmrs;
    }

    public function getKhachHang($MSKH)
    {
        $sql = "SELECT * FROM KhachHang kh, DiaChiKH dc WHERE kh.MSKH = dc.MSKH AND kh.MSKH=?";
        $stmt = $this->cnn->prepare($sql);
        $stmt->bind_param("i", $MSKH);

        $cstmr = [];

        if ($stmt->execute()) {
            $cstmr = $stmt->get_result()->fetch_assoc();
        }

        return $cstmr;
    }

    // Cập nhật khách hàng
    public function updateKhachHang($cst)
    {
        // Cập nhật khách hàng
        $sql = "UPDATE KhachHang SET HoTenKH = ?, TenCongTy = ?, SoDienThoai = ?, Email = ? WHERE MSKH = ?";
        $stmt = $this->cnn->prepare($sql);
        $stmt->bind_param("ssssi", $cst["HoTenKH"], $cst["TenCongTy"], $cst["SoDienThoai"], $cst["Email"], $cst["MSKH"]);

        // Cập nhật địa chỉ
        $sql1 = "UPDATE DiaChiKH SET DiaChi = ? WHERE MaDC = ?;";
        $stmt1 = $this->cnn->prepare($sql1);
        $stmt1->bind_param("si", $cst["DiaChi"], $cst["MaDC"]);

        return $stmt->execute() && $stmt1->execute();
    }

    // Xóa khách hàng
    public function deleteKhachHang($MSKH)
    {
        // Cập nhật khách hàng
        $sql = "DELETE FROM KhachHang WHERE MSKH = ?";
        $stmt = $this->cnn->prepare($sql);
        $stmt->bind_param("i", $MSKH);

        // Cập nhật địa chỉ
        $sql1 = "DELETE FROM DiaChiKH WHERE MSKH = ?;";
        $stmt1 = $this->cnn->prepare($sql1);
        $stmt1->bind_param("i", $MSKH);

        return $stmt->execute() && $stmt1->execute();
    }

    // Thêm khách hàng
    public function addKhachHang($cst)
    {
        // Cập nhật khách hàng
        $sql = "CALL spAddKhachHang(?, ?, ?, ?, ?);";
        $stmt = $this->cnn->prepare($sql);
        $stmt->bind_param("sssss", $cst["HoTenKH"], $cst["TenCongTy"], $cst["SoDienThoai"], $cst["Email"], $cst["DiaChi"]);

        return $stmt->execute();
    }
}

/* #endregion */ // Cửa hàng sắt
// Loại - Tên lấy từ csdl
// Quy cách chiều dài(mét)-cân nặng(kg)/mét
// Giá vnd đồng

/* #region  Dummy */
$typess = [
    "h" => "Hộp",
    "vu" => "Vuông",
    "i" => "I",
    "o" => "Ống",
    "v" => "V",
    "l" => "La",
    "v-l" => "V Lỗ"
];

static $Items = [
    [
        "MSHH" => "1",
        "TenHH" => "Hộp 5-10",
        "QuyCach" => "6-2.35",
        "Gia" => "350.000",
        "SoLuongHang" => "200",
        "TenLoai" => "Hộp",
        "GhiChu" => ""
    ],
    [
        "MSHH" => "2",
        "TenHH" => "Vuông-4",
        "QuyCach" => "6-1.25",
        "Gia" => "111.800",
        "SoLuongHang" => "100",
        "TenLoai" => "Vuông",
        "GhiChu" => "",
    ],
    [
        "MSHH" => "3",
        "TenHH" => "I-100",
        "QuyCach" => "6-9.46",
        "Gia" => "635.000",
        "SoLuongHang" => "50",
        "TenLoai" => "I",
        "GhiChu" => ""
    ],
    [
        "MSHH" => "4",
        "TenHH" => "Ống-34",
        "QuyCach" => "6-1.1",
        "Gia" => "96.000",
        "SoLuongHang" => "150",
        "TenLoai" => "Ống",
        "GhiChu" => ""
    ],
    [
        "MSHH" => "5",
        "TenHH" => "V-4",
        "QuyCach" => "6-0.7",
        "Gia" => "146.200",
        "SoLuongHang" => "120",
        "TenLoai" => "V",
        "GhiChu" => ""
    ],
    [
        "MSHH" => "6",
        "TenHH" => "La-3",
        "QuyCach" => "3-0.7",
        "Gia" => "19.000",
        "SoLuongHang" => "50",
        "TenLoai" => "La",
        "GhiChu" => ""
    ],
    [
        "MSHH" => "7",
        "TenHH" => "V4-Lỗ",
        "QuyCach" => "3-0.5",
        "Gia" => "30.000",
        "SoLuongHang" => "50",
        "TenLoai" => "V Lỗ",
        "GhiChu" => ""
    ]
];
/* #endregion */
$item = $Items[0];
$item["MaLoaiHang"] = "1";
$item["TenHH"] = "Vuông-5";
$item["Location"] = "h-5x10.jpg";
$item["Gia"] = 350001;
// $item["QuyCach"] = "6x3.2";

// var_dump($item);
// var_dump($dao->deleteItem(2));
// var_dump($dao->addItem($item));
// var_dump($dao->getItemById(8));
