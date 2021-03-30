<?php

declare(strict_types=1);
/**
 *
 * This is my open source code, please do not use it for commercial applications.
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 *
 * @author CodingHePing<847050412@qq.com>
 * @link   https://github.com/Hyperf-Glory/socket-io
 */
namespace App\Component\Command;

class CliTable
{
    /**
     * Table Data.
     *
     * @var
     */
    protected array $injectedData;

    /**
     * Table Item name.
     *
     * @var string
     */
    protected string $itemName = 'Row';

    /**
     * Table fields.
     *
     * @var array
     */
    protected array $fields = [];

    /**
     * Show column headers?
     *
     * @var bool
     */
    protected bool $showHeaders = true;

    /**
     * Use colors?
     *
     * @var bool
     */
    protected bool $useColors = true;

    /**
     * Table Border Color.
     *
     * @var string
     */
    protected string $tableColor = 'reset';

    /**
     * Header Color.
     *
     * @var string
     */
    protected string $headerColor = 'reset';

    /**
     * Colors, will be populated after instantiation.
     *
     * @var array
     */
    protected array $colors = [];

    /**
     * Border Characters.
     *
     * @var array
     */
    protected array $chars = [
        'top' => '═',
        'top-mid' => '╤',
        'top-left' => '╔',
        'top-right' => '╗',
        'bottom' => '═',
        'bottom-mid' => '╧',
        'bottom-left' => '╚',
        'bottom-right' => '╝',
        'left' => '║',
        'left-mid' => '╟',
        'mid' => '─',
        'mid-mid' => '┼',
        'right' => '║',
        'right-mid' => '╢',
        'middle' => '│ ',
    ];

    /**
     * Constructor.
     *
     * @param string $itemName
     * @param bool $useColors
     */
    public function __construct($itemName = 'Row', $useColors = true)
    {
        $this->setItemName($itemName);
        $this->setUseColors($useColors);
        $this->defineColors();
    }

    /**
     * setUseColors.
     *
     * @param bool $bool
     */
    public function setUseColors(bool $bool) : void
    {
        $this->useColors = (bool) $bool;
    }

    /**
     * getUseColors.
     *
     * @return bool
     */
    public function getUseColors() : bool
    {
        return $this->useColors;
    }

    /**
     * setTableColor.
     *
     * @param string $color
     */
    public function setTableColor(string $color) : void
    {
        $this->tableColor = $color;
    }

    /**
     * getTableColor.
     *
     * @return string
     */
    public function getTableColor() : string
    {
        return $this->tableColor;
    }

    /**
     * setChars.
     *
     * @param array $chars
     */
    public function setChars($chars) : void
    {
        $this->chars = $chars;
    }

    /**
     * setHeaderColor.
     *
     * @param string $color
     */
    public function setHeaderColor(string $color) : void
    {
        $this->headerColor = $color;
    }

    /**
     * getHeaderColor.
     *
     * @return string
     */
    public function getHeaderColor() : string
    {
        return $this->headerColor;
    }

    /**
     * setItemName.
     *
     * @param string $name
     */
    public function setItemName(string $name) : void
    {
        $this->itemName = $name;
    }

    /**
     * getItemName.
     *
     * @return string
     */
    public function getItemName() : string
    {
        return $this->itemName;
    }

    /**
     * injectData.
     *
     * @param array $data
     */
    public function injectData(array $data) : void
    {
        $this->injectedData = $data;
    }

    /**
     * setShowHeaders.
     *
     * @param bool $bool
     */
    public function setShowHeaders(bool $bool) : void
    {
        $this->showHeaders = $bool;
    }

    /**
     * getShowHeaders.
     *
     * @return bool
     */
    public function getShowHeaders() : bool
    {
        return $this->showHeaders;
    }

    /**
     * addField.
     *
     * @param string      $fieldName
     * @param string      $fieldKey
     * @param bool|object $manipulator
     * @param string      $color
     */
    public function addField(string $fieldName, string $fieldKey, $manipulator = false, $color = 'reset') : void
    {
        $this->fields[$fieldKey] = [
            'name' => $fieldName,
            'key' => $fieldKey,
            'manipulator' => $manipulator,
            'color' => $color,
        ];
    }

    /**
     * get.
     *
     * @return string
     */
    public function get() : string
    {
        $rowCount = 0;
        $columnLengths = [];
        $headerData = [];
        $cellData = [];

        // Headers
        if ($this->getShowHeaders()) {
            foreach ($this->fields as $field) {
                $headerData[$field['key']] = trim($field['name']);

                // Column Lengths
                if (! isset($columnLengths[$field['key']])) {
                    $columnLengths[$field['key']] = 0;
                }
                $columnLengths[$field['key']] = max($columnLengths[$field['key']], strlen(trim($field['name'])));
            }
        }

        // Data
        if ($this->injectedData !== null) {
            if (count($this->injectedData)) {
                foreach ($this->injectedData as $row) {
                    // Row
                    $cellData[$rowCount] = [];
                    foreach ($this->fields as $field) {
                        $key = $field['key'];
                        $value = $row[$key];
                        if ($field['manipulator'] instanceof CliTableManipulator) {
                            $value = trim($field['manipulator']->manipulate($value, $row, $field['name']));
                        }

                        $cellData[$rowCount][$key] = $value;

                        // Column Lengths
                        if (! isset($columnLengths[$key])) {
                            $columnLengths[$key] = 0;
                        }
                        $columnLengths[$key] = max($columnLengths[$key], strlen($value));
                    }
                    ++$rowCount;
                }
            } else {
                return 'There are no ' . $this->getPluralItemName() . PHP_EOL;
            }
        } else {
            return 'There is no injected data for the table!' . PHP_EOL;
        }

        $response = '';

        // Now draw the table!
        $response .= $this->getTableTop($columnLengths);
        if ($this->getShowHeaders()) {
            $response .= $this->getFormattedRow($headerData, $columnLengths, true);
            $response .= $this->getTableSeperator($columnLengths);
        }

        foreach ($cellData as $row) {
            $response .= $this->getFormattedRow($row, $columnLengths);
        }

        $response .= $this->getTableBottom($columnLengths);

        return $response;
    }

    /**
     * display.
     */
    public function display() : void
    {
        echo $this->get();
    }

    /**
     * getPluralItemName.
     *
     * @return string
     */
    protected function getPluralItemName()
    {
        if (count($this->injectedData) === 1) {
            return $this->getItemName();
        }
        $lastChar = strtolower(substr($this->getItemName(), strlen($this->getItemName()) - 1, 1));
        if ($lastChar === 's') {
            return $this->getItemName() . 'es';
        }
        if ($lastChar === 'y') {
            return substr($this->getItemName(), 0, -1) . 'ies';
        }
        return $this->getItemName() . 's';
    }

    /**
     * getFormattedRow.
     *
     * @param array $rowData
     * @param array $columnLengths
     * @param bool  $header
     *
     * @return string
     */
    protected function getFormattedRow(array $rowData, array $columnLengths, $header = false) : string
    {
        $response = $this->getChar('left');

        foreach ($rowData as $key => $field) {
            if ($header) {
                $color = $this->getHeaderColor();
            } else {
                $color = $this->fields[$key]['color'];
            }

            $fieldLength = mb_strwidth($field) + 1;
            $field = ' ' . ($this->getUseColors() ? $this->getColorFromName($color) : '') . $field;
            $response .= $field;

            for ($x = $fieldLength; $x < ($columnLengths[$key] + 2); ++$x) {
                $response .= ' ';
            }
            $response .= $this->getChar('middle');
        }

        return substr($response, 0, -3) . $this->getChar('right') . PHP_EOL;
    }

    /**
     * getTableTop.
     *
     * @param array $columnLengths
     *
     * @return string
     */
    protected function getTableTop(array $columnLengths) : string
    {
        $response = $this->getChar('top-left');
        foreach ($columnLengths as $length) {
            $response .= $this->getChar('top', $length + 2);
            $response .= $this->getChar('top-mid');
        }
        return substr($response, 0, -3) . $this->getChar('top-right') . PHP_EOL;
    }

    /**
     * getTableBottom.
     *
     * @param array $columnLengths
     *
     * @return string
     */
    protected function getTableBottom(array $columnLengths) : string
    {
        $response = $this->getChar('bottom-left');
        foreach ($columnLengths as $length) {
            $response .= $this->getChar('bottom', $length + 2);
            $response .= $this->getChar('bottom-mid');
        }
        return substr($response, 0, -3) . $this->getChar('bottom-right') . PHP_EOL;
    }

    /**
     * getTableSeperator.
     *
     * @param array $columnLengths
     *
     * @return string
     */
    protected function getTableSeperator(array $columnLengths) : string
    {
        $response = $this->getChar('left-mid');
        foreach ($columnLengths as $length) {
            $response .= $this->getChar('mid', $length + 2);
            $response .= $this->getChar('mid-mid');
        }
        return substr($response, 0, -3) . $this->getChar('right-mid') . PHP_EOL;
    }

    /**
     * getChar.
     *
     * @param string $type
     * @param int $length
     * @return string
     */
    protected function getChar($type, $length = 1) : string
    {
        $response = '';
        if (isset($this->chars[$type])) {
            if ($this->getUseColors()) {
                $response .= $this->getColorFromName($this->getTableColor());
            }
            $char = trim($this->chars[$type]);
            $response .= str_repeat($char, $length);
        }
        return $response;
    }

    /**
     * defineColors.
     */
    protected function defineColors() : void
    {
        $this->colors = [
            'blue' => chr(27) . '[1;34m',
            'red' => chr(27) . '[1;31m',
            'green' => chr(27) . '[1;32m',
            'yellow' => chr(27) . '[1;33m',
            'black' => chr(27) . '[1;30m',
            'magenta' => chr(27) . '[1;35m',
            'cyan' => chr(27) . '[1;36m',
            'white' => chr(27) . '[1;37m',
            'grey' => chr(27) . '[0;37m',
            'reset' => chr(27) . '[0m',
        ];
    }

    /**
     * getColorFromName.
     *
     * @param string $colorName
     *
     * @return string
     */
    protected function getColorFromName(string $colorName) : string
    {
        return $this->colors[$colorName] ?? $this->colors['reset'];
    }
}
