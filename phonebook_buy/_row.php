<?
$num = isset($row['num']) ? $row['num'] : '';
$vendor_code = isset($row['vendor_code']) ? $row['vendor_code'] : '';
$vendor_name = isset($row['vendor_name']) ? $row['vendor_name'] : '';
$representative_name = isset($row['representative_name']) ? $row['representative_name'] : '';
$address = isset($row['address']) ? $row['address'] : '';
$business_type = isset($row['business_type']) ? $row['business_type'] : '';
$item_type = isset($row['item_type']) ? $row['item_type'] : '';
$phone = isset($row['phone']) ? $row['phone'] : '';
$mobile = isset($row['mobile']) ? $row['mobile'] : '';
$email = isset($row['email']) ? $row['email'] : '';
$fax = isset($row['fax']) ? $row['fax'] : '';
$manager_name = isset($row['manager_name']) ? $row['manager_name'] : '';
$contact_info = isset($row['contact_info']) ? $row['contact_info'] : '';
$note = isset($row['note']) ? $row['note'] : '';
$is_deleted = isset($row['is_deleted']) ? $row['is_deleted'] : 0;
$searchtag = isset($row['searchtag']) ? $row['searchtag'] : '';
$update_log = isset($row['update_log']) ? $row['update_log'] : '';
$item = isset($row['item']) ? $row['item'] : '';
// 새 컬럼
$is_china_vendor = isset($row['is_china_vendor']) ? (int)$row['is_china_vendor'] : 0;
$image_base64 = isset($row['image_base64']) ? $row['image_base64'] : '';
// 새 컬럼: 카테고리
$category = isset($row['category']) ? $row['category'] : '';
// 새 컬럼: 중국발주업체 순서
$china_sort_order = isset($row['china_sort_order']) ? (int)$row['china_sort_order'] : 999;
?> 