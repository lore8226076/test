<?php
namespace App\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

use Storage;
/**
 * PhpOfficeService
 */
class PhpOfficeService
{
    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    public function import($file, $column = [], $sheet = 0, $start_row = 1)
    {
        $arr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
                'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
                'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ',
                'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ',
                'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG', 'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN', 'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU', 'DV', 'DW', 'DX', 'DY', 'DZ',
            ];
        if (empty($column)) {
            $column = $arr;
        }

        $spreadsheet = IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();

        $highestRow = $worksheet->getHighestRow(); // e.g. 10
        $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'


        $data = [];
        $i = 0;
        // Loop through each row of the worksheet in turn
        for ($row = 1; $row <= $highestRow; $row++) {
            if($row > $start_row){
                // Read a row of data into an array
                $rowData = $worksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
                foreach ($rowData as $rowD) {
                    $bool = 0;
                    foreach ($rowD as $key => $value) {
                        if ($key < count($column)) {
                            if ($value) {
                                $bool++;
                            }
                            $data[$i][$column[$key]] = $value;
                        }
                    }
                    if ($bool < 1) {
                        unset($data[$i]);
                    }
                    $i++;
                }
            }
            // Print the row data
        }
        return $data;
    }

    public function num2alpha($n)  //數字轉英文(0=>A、1=>B、26=>AA...以此類推)
    {
        for($r = ""; $n >= 0; $n = intval($n / 26) - 1)
            $r = chr($n%26 + 0x41) . $r;
        return $r;
    }
    public function alpha2num($a)  //英文轉數字(A=>0、B=>1、AA=>26...以此類推)
    {
        $l = strlen($a);
        $n = 0;
        for($i = 0; $i < $l; $i++)
            $n = $n*26 + ord($a[$i]) - 0x40;
        return $n-1;
    }

    public function export($fileName, $data, $format = false, $dir = '', $disk = false)
    {
        $disk = $disk ?: config('services.filesystem.disk');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($data as $index => $value) {
            if (isset($format['styleType'][$index])) {
                foreach ($format['styleType'][$index] as $key => $type) {
                    switch ($type) {
                        case 'setWrapText':
                            $sheet->getStyle($index)->getAlignment()->setWrapText(true);
                            break;
                        default:
                            break;
                    }
                }
            }
            if (isset($format['dataType'][$index])) {
                switch ($format['dataType'][$index]) {
                    case 'string':
                        $sheet->setCellValueExplicit($index, $value, DataType::TYPE_STRING);
                        break;
                    default:
                        $sheet->setCellValue($index, $value);
                        break;
                }
            } else {
                $sheet->setCellValue($index, $value);
            }
        }

        if (isset($format['aligns']) && is_array($format['aligns'])) {
            foreach ($format['aligns'] as $align) {
                if (count($align) == 2) {
                    if ($align[1] == 'L') {
                        $alignType = Alignment::HORIZONTAL_LEFT;
                    } elseif ($align[1] == 'R') {
                        $alignType = Alignment::HORIZONTAL_RIGHT;
                    } else {
                        $alignType = Alignment::HORIZONTAL_CENTER;
                    }
                    $sheet->getStyle($align[0])->getAlignment()->setHorizontal($alignType);
                }
            }
        }
        if (isset($format['columntWidth']) && is_array($format['columntWidth'])) {
            foreach ($format['columntWidth'] as $column => $width) {
                $sheet->getColumnDimension($column)->setWidth($width);
            }
        }
        if (isset($format['mergeCells']) && is_array($format['mergeCells'])) {
            foreach ($format['mergeCells'] as $cells) {
                $sheet->mergeCells($cells); //'A1:A3'
            }
        }

        if (isset($format['removeColumn']) && is_array($format['removeColumn'])) {
            arsort($format['removeColumn']);
            foreach ($format['removeColumn'] as $cells) {
                $sheet->removeColumnByIndex($cells);
            }
        }

        foreach (range('A', $sheet->getHighestColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        $sheet->getStyle($sheet->calculateWorksheetDimension())
          ->getAlignment()
          ->setVertical(Alignment::VERTICAL_CENTER);

        $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
        ob_end_clean();

        // 将文件保存到本地临时目录
        $tempFilePath = tempnam(sys_get_temp_dir(), 'excel');
        $objWriter->save($tempFilePath);

        // 定义 disk 文件路径
        $filePath = 'temp_download/'. $dir . '/' . $fileName;

        // 上传文件到 disk
        Storage::disk($disk)->put($filePath, file_get_contents($tempFilePath));

        // 删除本地临时文件
        unlink($tempFilePath);

        unset($spreadsheet);
        unset($objWriter);

        return Storage::disk($disk)->url($filePath);
    }
}
