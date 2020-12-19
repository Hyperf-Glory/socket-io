<?php

declare(strict_types=1);
/**
 *
 * This file is part of the My App.
 *
 * Copyright CodingHePing 2016-2020.
 *
 * This is my open source code, please do not use it for commercial applications.
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 *
 * @author CodingHePing<847050412@qq.com>
 * @link   https://github.com/codingheping/hyperf-chat-upgrade
 */
namespace App\Component\Command;

class CliTableManipulator
{
    /**
     * Stores the type of manipulation to perform.
     *
     * @var string
     */
    protected $type = '';

    /**
     * Constructor.
     *
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * manipulate
     * This is used by the Table class to manipulate the data passed in and returns the formatted data.
     *
     * @param mixed $value
     * @param array $row
     * @param string $fieldName
     * @return string
     */
    public function manipulate($value, $row = [], $fieldName = '')
    {
        $type = $this->type;
        if ($type && is_callable([$this, $type])) {
            return $this->{$type}($value, $row, $fieldName);
        }
        return $value . ' (Invalid Type: "' . $type . '")';
    }

    /**
     * dollar
     * Changes 12300.23 to $12,300.23.
     *
     * @param mixed $value
     */
    protected function dollar($value): string
    {
        return '$' . number_format($value, 2);
    }

    /**
     * date
     * Changes 1372132121 to 25-06-2013.
     *
     * @param mixed $value
     * @return string
     */
    protected function date($value)
    {
        if (! $value) {
            return 'Not Recorded';
        }
        return date('d-m-Y', $value);
    }

    /**
     * datelong
     * Changes 1372132121 to 25th June 2013.
     *
     * @param mixed $value
     * @return string
     */
    protected function datelong($value)
    {
        if (! $value) {
            return 'Not Recorded';
        }
        return date('jS F Y', $value);
    }

    /**
     * time
     * Changes 1372132121 to 1:48 pm.
     *
     * @param mixed $value
     * @return string
     */
    protected function time($value)
    {
        if (! $value) {
            return 'Not Recorded';
        }
        return date('g:i a', $value);
    }

    /**
     * datetime
     * Changes 1372132121 to 25th June 2013, 1:48 pm.
     *
     * @param mixed $value
     * @return string
     */
    protected function datetime($value)
    {
        if (! $value) {
            return 'Not Recorded';
        }
        return date('jS F Y, g:i a', $value);
    }

    /**
     * nicetime
     * Changes 1372132121 to 25th June 2013, 1:48 pm
     * Changes 1372132121 to Today, 1:48 pm
     * Changes 1372132121 to Yesterday, 1:48 pm.
     *
     * @param mixed $value
     * @return string
     */
    protected function nicetime($value)
    {
        if (! $value) {
            return '';
        }
        if ($value > mktime(0, 0, 0, (int) date('m'), (int) date('d'), (int) date('Y'))) {
            return 'Today ' . date('g:i a', $value);
        }
        if ($value > mktime(0, 0, 0, (int) date('m'), (int) date('d') - 1, (int) date('Y'))) {
            return 'Yesterday ' . date('g:i a', $value);
        }
        return date('jS F Y, g:i a', $value);
    }

    /**
     * duetime.
     *
     * @param mixed $value
     * @return string
     */
    protected function duetime($value)
    {
        if (! $value) {
            return '';
        }
        $isPast = false;
        if ($value > time()) {
            $seconds = $value - time();
        } else {
            $isPast = true;
            $seconds = time() - $value;
        }

        $text = $seconds . ' second' . ($seconds === 1 ? '' : 's');
        if ($seconds >= 60) {
            $minutes = floor($seconds / 60);
            $seconds -= ($minutes * 60);
            $text = $minutes . ' minute' . ($minutes === 1 ? '' : 's');
            if ($minutes >= 60) {
                $hours = floor($minutes / 60);
                $minutes -= ($hours * 60);
                $text = $hours . ' hours, ' . $minutes . ' minute' . ($hours === 1 ? '' : 's');
                if ($hours >= 24) {
                    $days = floor($hours / 24);
                    $hours -= ($days * 24);
                    $text = $days . ' day' . ($days === 1 ? '' : 's');
                    if ($days >= 365) {
                        $years = floor($days / 365);
                        $days -= ($years * 365);
                        $text = $years . ' year' . ($years === 1 ? '' : 's');
                    }
                }
            }
        }

        return $text . ($isPast ? ' ago' : '');
    }

    /**
     * nicenumber.
     *
     * @param int $value
     * @return string
     */
    protected function nicenumber($value)
    {
        return number_format($value, 0);
    }

    /**
     * month
     * Changes 1372132121 to June.
     *
     * @param mixed $value
     * @return string
     */
    protected function month($value)
    {
        if (! $value) {
            return 'Not Recorded';
        }
        return date('F', $value);
    }

    /**
     * year
     * Changes 1372132121 to 2013.
     *
     * @param mixed $value
     * @return string
     */
    protected function year($value)
    {
        if (! $value) {
            return 'Not Recorded';
        }
        return date('Y', $value);
    }

    /**
     * monthyear
     * Changes 1372132121 to June 2013.
     *
     * @param mixed $value
     * @return string
     */
    protected function monthyear($value)
    {
        if (! $value) {
            return 'Not Recorded';
        }
        return date('F Y', $value);
    }

    /**
     * percent
     * Changes 50.2 to 50%.
     *
     * @param mixed $value
     * @return string
     */
    protected function percent($value)
    {
        return intval($value) . '%';
    }

    /**
     * yesno
     * Changes 0/false and 1/true to No and Yes respectively.
     *
     * @param mixed $value
     * @return string
     */
    protected function yesno($value)
    {
        return $value ? 'Yes' : 'No';
    }

    /**
     * text
     * Strips input of any html.
     *
     * @param mixed $value
     * @return string
     */
    protected function text($value)
    {
        return strip_tags($value);
    }
}
