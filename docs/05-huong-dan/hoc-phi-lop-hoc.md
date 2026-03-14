# 05A - Van hanh hoc phi theo lop hoc

## 1. Tong quan

He thong da chuyen tu mo hinh `hocphi` theo `khoahoc` sang mo hinh gia ban theo `lop hoc`.

- `khoahoc` chi mo ta chuong trinh dao tao.
- `lophoc` la don vi van hanh va la noi gan chinh sach gia.
- `dangkylophoc` luu `snapshot` gia tai thoi diem dang ky.
- `hoadon` doc tu snapshot cua dang ky, khong doc lai gia hien tai cua lop.

Muc tieu cua mo hinh nay:

- Cho phep tao lop hoc truoc khi chot hoc phi.
- Khong de so buoi thuc te tu dong lam thay doi cong no da chot.
- Cho phep doi gia cho hoc vien moi ma khong anh huong hoc vien da dang ky.

## 2. Bang du lieu chinh

### 2.1 `lophoc`

- Quan ly van hanh lop: giao vien, co so, phong hoc, ca hoc, lich hoc, trang thai.
- Co the tao khi chua co hoc phi.

### 2.2 `lophoc_chinhsachgia`

- Mỗi lop toi da co 1 chinh sach gia dang ap dung.
- Cot quan trong:
  - `loaiThu`
  - `hocPhiNiemYet`
  - `soBuoiCamKet`
  - `ghiChuChinhSach`
  - `hieuLucTu`, `hieuLucDen`
  - `trangThai`

Phase hien tai van hanh theo mo hinh chinh:

- `TRON_GOI`: gia tron lop

Hai loai con lai duoc de san de mo rong:

- `THEO_THANG`
- `THEO_DOT`

### 2.3 `lophoc_dotthu`

- Luu cau hinh thu theo dot cua 1 chinh sach gia.
- Dung cho cac dot nhu: coc, khai giang, giua khoa.
- Tong `soTien` cac dot phai bang `hocPhiNiemYet`.

### 2.4 `dangkylophoc`

- Luu dang ky hoc vien vao lop.
- Chup snapshot gia tai thoi diem dang ky:
  - `lopHocChinhSachGiaId`
  - `loaiThuSnapshot`
  - `hocPhiNiemYetSnapshot`
  - `giamGiaSnapshot`
  - `hocPhiPhaiThuSnapshot`
  - `soBuoiCamKetSnapshot`
  - `ghiChuGiaSnapshot`

### 2.5 `hoadon`

- Moi hoa don moi phai doc tong tien tu snapshot cua `dangkylophoc`.
- `lopHocDotThuId` duoc de san de map hoa don voi tung dot thu khi mo rong nghiep vu thu nhieu dot.

## 3. Luong van hanh admin

### 3.1 Tao khoa hoc

- Tao `khoahoc` de mo ta san pham dao tao, noi dung, slug, danh muc.
- Khong can tao hoc phi o buoc nay.

### 3.2 Tao lop hoc

- Tao `lophoc` voi giao vien, phong, co so, ca hoc, lich hoc, ngay bat dau.
- Lop o trang thai nhap co the chua co gia.

### 3.3 Cau hinh chinh sach gia cho lop

Tai form tao/sua lop:

- Nhap `hocPhiNiemYet`.
- Nhap `soBuoiCamKet` neu can tham chieu hop dong.
- Chon `loaiThu`.
- Them `ghiChuChinhSach` neu can.
- Neu thu theo dot, khai bao danh sach `lophoc_dotthu`.

Quy tac:

- Neu co dot thu, tong tien cac dot phai bang `hocPhiNiemYet`.
- Neu lop da co hoc vien dang ky, khong duoc xoa trang chinh sach gia.

### 3.4 Mo tuyen sinh

He thong chi cho phep lop chuyen sang cac trang thai van hanh neu da co chinh sach gia hop le:

- `DANG_TUYEN_SINH`
- `CHOT_DANH_SACH`
- `DANG_HOC`
- `DA_KET_THUC`

Trang thai `SAP_MO` co the chua co hoc phi.

## 4. Luong dang ky hoc vien

Khi hoc vien dang ky lop:

1. He thong kiem tra lop co dang cho phep dang ky khong.
2. He thong kiem tra lop co `lophoc_chinhsachgia` hop le khong.
3. He thong chup snapshot gia vao `dangkylophoc`.
4. He thong tao `hoadon` tu `hocPhiPhaiThuSnapshot`.

He qua nghiep vu:

- Sua gia lop sau nay khong doi du lieu dang ky cu.
- Cong no da phat sinh khong bi anh huong boi thay doi so buoi hoc.

## 5. Cach xu ly khi thay doi so buoi day

So buoi hoc la du lieu van hanh, khong phai cong thuc tai chinh runtime.

Neu phat sinh them/bot buoi:

- Khong cap nhat lai `hocPhiPhaiThuSnapshot` cua dang ky cu.
- Khong sua tong tien cua `hoadon` da tao.
- Neu can thu them:
  - tao dot thu bo sung, hoac
  - tao hoa don dieu chinh theo nghiep vu tai chinh.
- Neu can giam tru:
  - tao phieu dieu chinh, hoan tien, hoac cap nhat cong no theo quy trinh tai chinh noi bo.

## 6. Quy tac quan trong

- Khong quan ly hoc phi o cap `khoahoc`.
- Khong tinh tien truc tiep tu `soBuoiDuKien` hoac `soBuoiThucTe`.
- Khong update hoi to snapshot gia cua dang ky cu.
- Khong dung lai bang `hocphi` cu cho code moi.

## 7. Gioi han phase hien tai

He thong da co schema va giao dien cho `lophoc_dotthu`, nhung runtime billing hien tai van:

- tao 1 hoa don tong tien theo snapshot,
- chua tach thanh nhieu hoa don theo tung dot thu.

Neu can mo rong phase sau:

- moi `lophoc_dotthu` sinh 1 `hoadon`,
- cong no dang ky duoc tong hop tu nhieu hoa don con,
- trang thai thanh toan lop hoc doc tu tong cong no da thu.

## 8. Migration tu mo hinh cu

Migration `2026_03_14_150000_refactor_class_pricing_to_lophoc_chinhsachgia.php` thuc hien:

- Tao `lophoc_chinhsachgia`
- Tao `lophoc_dotthu`
- Backfill du lieu tu `hocphi` cu vao chinh sach gia cua lop
- Backfill snapshot gia vao `dangkylophoc`
- Them `lopHocDotThuId` vao `hoadon`
- Xoa `lophoc.hocPhiId`
- Xoa bang `hocphi`

## 9. Checklist van hanh

- Tao `khoahoc`
- Tao `lophoc`
- Cau hinh `lophoc_chinhsachgia`
- Kiem tra tong dot thu neu co
- Chuyen lop sang `DANG_TUYEN_SINH`
- Theo doi dang ky va hoa don phat sinh tu snapshot
