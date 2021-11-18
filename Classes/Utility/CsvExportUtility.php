<?php

namespace Othman\SaveFormToDb\Utility;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CsvExportUtility
 * To download emails as .csv file
 */
class CsvExportUtility
{
    protected string $delimiter = ';';
    protected array $csvFields;
    protected array $defaultCsvFields = ['uid', 'crdate'];

    public function __construct()
    {
        $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('othman_saveformtodb');
        $this->delimiter = $extensionConfiguration['delimiter'];
        $this->defaultCsvFields = GeneralUtility::trimExplode(',', $extensionConfiguration['defaultCsvFields']);
    }

    /**
     * @param array $dataList
     * @param string $formIdentifierPath
     * @throws \Exception
     */
    public function prepareEmailsAndDownloadAsCsv(array $dataList, string $formIdentifierPath)
    {
        $csvContent = '';
        $this->csvFields = $this->getCsvFields($formIdentifierPath);
        $csvContent .= $this->getCsvHeaderColumns();
        foreach ($dataList as $data) {
            $csvContent .= $this->convertJsonToCsv($data);
        }

        $this->csvContentDownload($csvContent);
    }

    /**
     * Get all renderables fields from Form
     * @param string $formIdentifierPath
     * @return mixed
     */
    protected function getCsvFields(string $formIdentifierPath)
    {
        $formUtility = GeneralUtility::makeInstance(FormUtility::class);
        return $formUtility->getRenderAblesFieldsOfYamlForm($formIdentifierPath);
    }

    /**
     * Build header of Csv file
     * @return string
     */
    protected function getCsvHeaderColumns()
    {
        $csvRow = '';
        $csvRow .= '"uid"' . $this->delimiter;
        foreach ($this->csvFields as $csvField) {
            if (!empty($csvField['label'])) {
                $csvRow .= '"' . $csvField['label'] . '"';
            } else {
                $csvRow .= '""';
            }
            $csvRow .= $this->delimiter;
        }
        $csvRow .= '"Date"' . "\n";
        return $csvRow;
    }

    /**
     * Convert json to csv
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function convertJsonToCsv(array $data)
    {
        /// xxxxx
        $csvRow = '';
        $dataFields = json_decode($data['values'], true);
        if (!is_array($data)) {
            $dataFields = json_decode($data['values'], true);
        }
        $csvRow .= '"' . $data['uid'] . '"' . $this->delimiter;
        foreach ($this->csvFields as $csvFieldsKey => $value) {
            if (!empty($dataFields[$csvFieldsKey]['value'])) {
                if (is_array($dataFields[$csvFieldsKey]['value'])) {
                    $csvRow .= '"' . implode(',', $dataFields[$csvFieldsKey]['value']) . '"';
                } else {
                    $csvRow .= '"' . $dataFields[$csvFieldsKey]['value'] . '"';
                }
            } else {
                $csvRow .= '""';
            }
            $csvRow .= $this->delimiter;
        }
        $date = new \DateTime('@' . $data['crdate']);
        $csvRow .= '"' . $date->format('Y.m.d H:i e') . '"';
        return $csvRow . "\n";
    }

    /**
     * Download Csv content
     * @param string $csvContent
     */
    public function csvContentDownload(string $csvContent)
    {
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=export.csv;');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . strlen($csvContent));

        $fp = fopen('php://output', 'w');
        fwrite($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
        if ($fp) {
            fputcsv($fp, json_decode($csvContent, true), $this->delimiter);
        }
        fclose($fp);
        die($csvContent);
    }
}
