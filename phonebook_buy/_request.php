<?
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';
$vendor_code = isset($_REQUEST['vendor_code']) ? $_REQUEST['vendor_code'] : '';
$vendor_name = isset($_REQUEST['vendor_name']) ? $_REQUEST['vendor_name'] : '';
$representative_name = isset($_REQUEST['representative_name']) ? $_REQUEST['representative_name'] : '';
$address = isset($_REQUEST['address']) ? $_REQUEST['address'] : '';
$business_type = isset($_REQUEST['business_type']) ? $_REQUEST['business_type'] : '';
$item_type = isset($_REQUEST['item_type']) ? $_REQUEST['item_type'] : '';
$phone = isset($_REQUEST['phone']) ? $_REQUEST['phone'] : '';
$mobile = isset($_REQUEST['mobile']) ? $_REQUEST['mobile'] : '';
$email = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';
$fax = isset($_REQUEST['fax']) ? $_REQUEST['fax'] : '';
$manager_name = isset($_REQUEST['manager_name']) ? $_REQUEST['manager_name'] : '';
$contact_info = isset($_REQUEST['contact_info']) ? $_REQUEST['contact_info'] : '';
$note = isset($_REQUEST['note']) ? $_REQUEST['note'] : '';
$is_deleted = isset($_REQUEST['is_deleted']) ? $_REQUEST['is_deleted'] : 0;
$searchtag = isset($_REQUEST['searchtag']) ? $_REQUEST['searchtag'] : '';
$update_log = isset($_REQUEST['update_log']) ? $_REQUEST['update_log'] : '';
$item = isset($_REQUEST['item']) ? $_REQUEST['item'] : '';
// 새 필드: 중국발주업체 여부(체크박스), 이미지(base64 데이터URL)
$is_china_vendor = isset($_REQUEST['is_china_vendor']) ? (int)$_REQUEST['is_china_vendor'] : 0;
$image_base64 = isset($_REQUEST['image_base64']) ? $_REQUEST['image_base64'] : '';
// 새 필드: 카테고리(콤마 구분 문자열)
$category = isset($_REQUEST['category']) ? $_REQUEST['category'] : '';
// 새 필드: 중국발주업체 순서
$china_sort_order = isset($_REQUEST['china_sort_order']) ? (int)$_REQUEST['china_sort_order'] : 999;

?>
 