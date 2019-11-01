<?php

namespace app\common;

class DateToolkit
{
    public static function timeToHour($times)
    {
        if (empty($times)) {
            return 0;
        }

        return substr(sprintf('%.2f', $times / 3600), 0, -1);
    }

    public static function convertSecondToHour($second)
    {
        if (empty($second)) {
            return 0;
        }

        return substr(sprintf('%.2f', $second / 3600), 0, -1);
    }

    public static function generateDate($timeType, $cursor = 0)
    {
        $supportTimeType = array('day', 'week', 'month', 'year');

        if (!in_array($timeType, $supportTimeType)) {
            throw new InvalidArgumentException('Unsupported time type');
        }

        $generateDate = strtotime(date('Y-m-d'));
        if (!empty($cursor)) {
            $generateDate = strtotime(date('Y-m-d', strtotime($cursor . $timeType)));
        }

        return $generateDate;
    }

    public static function generateStartDateAndEndDate($timeType, $cursor = 0, $format = 'date')
    {
        $startDate = 0;
        $endDate = 0;

        $supportTimeType = array('day', 'week', 'month', 'year');

        if (!in_array($timeType, $supportTimeType)) {
            throw new InvalidArgumentException('Unsupported time type');
        }

        if (!empty($cursor) && strpos($cursor, '-') === false) {
            throw new InvalidArgumentException('Unsupported cursor');
        }

        if ($timeType == 'day') {
            $startDate = date('Y-m-d', strtotime($cursor . $timeType));

            $endDate = date('Y-m-d');
        }

        if ($timeType == 'week') {
            $weekMondayTime = strtotime('Monday this week');
            $weekSundayTime = strtotime('SunDay this week');

            $startDate = date('Y-m-d', strtotime($cursor . $timeType, $weekMondayTime));
            $endDate = date('Y-m-d', strtotime($cursor . $timeType, $weekSundayTime));
        }

        if ($timeType == 'month') {
            if (in_array($cursor, array(0, -1))) {
                $startDate = date('Y-m-1', strtotime($cursor . $timeType));
                $endDate = date('Y-m-t', strtotime(empty($cursor) ? $cursor . $timeType : '-1 month'));
            } else {
                $startDate = date('Y-m-d', strtotime(date('Y-m', strtotime($cursor . 'month'))));
                $endDate = date('Y-m-d', time());
            }
        }

        if ($timeType == 'year') {
            $startDate = date('Y-1-1', strtotime($cursor . $timeType));
            $endDate = date('Y-12-31', strtotime($cursor . $timeType));
        }

        return ($format == 'date') ? array($startDate, $endDate) : array(strtotime($startDate), strtotime($endDate));
    }

    /**
     * Generate a date range starting from startDate to endDate.
     *
     * @param [type] $startDate [description]
     * @param [type] $endDate   [description]
     *
     * @return [type] [description]
     */
    public static function generateDateRange($startDate, $endDate)
    {
        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);

        $range = range($startTime, $endTime, 3600 * 24);
        array_walk($range, function (&$value) {
            $value = date('Y-m-d', $value);
        });

        return $range;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param int $interval
     *
     * @return \DatePeriod
     */
    public static function getPeriod($startDate, $endDate, $interval = 1)
    {
        $begin = new \DateTime($startDate);
        $end = new \DateTime($endDate);

        $interval = \DateInterval::createFromDateString("{$interval} days");

        return new \DatePeriod($begin, $interval, $end);
    }
}
