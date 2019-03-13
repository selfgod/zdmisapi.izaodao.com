<?php
namespace Lib;

class Export
{
    /**
     * 导出Excel
     * @param $key
     * @param $uid
     * @param $title
     * @param $dataCallback
     * @param int $limit
     * @return bool|string
     */
    public static function export($key, $uid, $title, $dataCallback, $limit = 500)
    {
        if (empty($title)) {
            return FALSE;
        }
        $page = 0;
        $writer = new XLSXWriter();
        $dir = EASYSWOOLE_ROOT . DIRECTORY_SEPARATOR . 'Export';
        $filename = $key . '_' . $uid . '-' . date('Y-m-d') . '_' . time() . '.xlsx';
        $sheet1 = 'sheet1';
        $width = [];
        for ($i = 0;$i < count($title);$i++) {
            $width[] = 20;
        }
        $writer->writeSheetHeader($sheet1, $title, ['widths' => $width, 'freeze_rows' => 1, 'font-size' => 14]);
        while (1) {
            $list = $dataCallback($page, $limit);
            if (empty($list)) {
                break;
            }
            foreach ($list as $item) {
                $writer->writeSheetRow($sheet1, array_values($item), ['font-size' => 12]);
            }
            if (count($list) < $limit) {
                break;
            }
            $page++;
        }
        $writer->writeToFile($dir . DIRECTORY_SEPARATOR . $filename);
        return $filename;
    }
}