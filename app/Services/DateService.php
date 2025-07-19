<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DateService
{
    public static function now()
    {
        return Carbon::now();
    }
    public static function today()
    {
        return Carbon::today();
    }

    public static function currentStartOfWeek()
    {
        return Carbon::now()->startOfWeek();
    }

    public static function currentEndOfWeek()
    {
        return Carbon::now()->endOfWeek();
    }

    public static function currentMonth()
    {
        return Carbon::now()->month;
    }

    public static function currentYear()
    {
        return Carbon::now()->year;
    }

    public static function currentLastDays(int $days)
    {
        return Carbon::today()->subDays($days);
    }

    public static function currentNextDays(int $days)
    {
        return Carbon::today()->addDays($days);
    }


    /**
     * Parses a time string into a Carbon instance.
     *
     * This method accepts time strings in various formats:
     * - "HH" (e.g., "12")
     * - "HH:MM" (e.g., "12:30")
     * - "HH:MM:SS" (e.g., "12:30:45")
     *
     * If the provided time string matches one of these formats, it extracts the
     * hours, minutes, and seconds. If minutes or seconds are not provided, they
     * default to 0.
     *
     * @param string $time The time string to parse.
     * @return \Carbon\Carbon|null Returns a Carbon instance representing the
     * parsed time, or null if the format is invalid.
     */
    public static function parseTime($time)
    {
        // Verifica se o formato é HH:MM:SS, HH:MM ou HH
        if (preg_match('/^(\d{1,2})(?::(\d{2}))?(?::(\d{2}))?$/', $time, $matches)) {
            $hours = intval($matches[1]);
            $minutes = isset($matches[2]) ? intval($matches[2]) : 0; // Default para 0 se não houver minutos
            $seconds = isset($matches[3]) ? intval($matches[3]) : 0; // Default para 0 se não houver segundos

            // Validando os intervalos de horas, minutos e segundos
            if ($hours < 0 || $hours > 23 || $minutes < 0 || $minutes > 59 || $seconds < 0 || $seconds > 59) {
                return null; // Retorna null se algum valor estiver fora dos limites
            }

            // Retorna um objeto Carbon se o tempo for válido
            return Carbon::createFromTime($hours, $minutes, $seconds);
        }

        // Retorna null se o formato for inválido
        return null;
    }

    /**
     * Parses a date string into a Carbon instance using various formats.
     *
     * This method attempts to interpret a given date string using a list of
     * predefined formats. It supports a wide range of date and time formats,
     * including:
     * - Standard formats: "Y-m-d H:i:s", "d/m/Y", "m-d-Y", etc.
     * - ISO 8601 formats: "Y-m-d\TH:i:s", "Y-m-d H:i:sP", etc.
     * - RFC 2822 formats: "D, d M Y H:i:s O", "D, d M Y H:i:s T", etc.
     * - Unix timestamp (e.g., "U").
     *
     * If the input date string matches one of the formats, it will return a
     * Carbon instance representing the parsed date and time. If none of the
     * formats match the input string, it returns null.
     *
     * @param string $date The date string to parse.
     * @return \Carbon\Carbon|null Returns a Carbon instance representing the
     * parsed date, or null if none of the formats match the input.
     */

    public static function parseDate(string $date)
    {
        // List of formats to try
        $formats = [
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d H',
            'Y-m-d',
            'Y/m/d H:i:s',
            'Y/m/d H:i',
            'Y/m/d',
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'd/m/Y',
            'd.m.Y H:i:s',
            'd.m.Y H:i',
            'd.m.Y',

            'm-d-Y H:i:s',
            'm-d-Y H:i',
            'm-d-Y',
            'U', // Timestamp Unix
            'D, d M Y H:i:s O', // RFC 2822
            'D, d M Y H:i:s T', // RFC 2822
            'Y-m-d\TH:i:sP', // ISO 8601
            'Y-m-d\TH:i:s', // ISO 8601
            'Y-m-d\TH:i', // ISO 8601
            'Y-m-d H:i:sP', // com zona horária
            'Y-m-d H:i:sO', // com zona horária
        ];

        // Try to parse the date using the given formats
        foreach ($formats as $format) {
            try {
                // Attempt to parse the date
                $parsedDate = Carbon::createFromFormat($format, $date);

                // Check if the parsed date matches the exact input
                if ($parsedDate && $parsedDate->format($format) === $date) {
                    return $parsedDate;
                }
            } catch (\Exception $e) {
                // Continue trying other formats
                continue;
            }
        }

        // If no format matches, return null
        return null;
    }

    /**
     * Calculates the difference between two dates in various time units.
     *
     * This method takes two date inputs, parses them, and computes the
     * difference in multiple units, including hours, days, weeks,
     * fortnights, months, bimesters, quadrimesters, semesters, and years.
     *
     * The dates are parsed using the `parseDate` method. If either of
     * the dates is invalid, an exception will be thrown indicating
     * the invalid format.
     *
     * @param mixed $startDate The starting date, which can be a string
     *                         or a Carbon instance.
     * @param mixed $endDate The ending date, which can also be a string
     *                       or a Carbon instance.
     *
     * @return array An associative array containing the differences
     *               in various time units. The keys include:
     *               - 'hours': Difference in hours
     *               - 'days': Difference in days
     *               - 'weeks': Difference in weeks
     *               - 'fortnights': Difference in fortnights (1 fortnight = 14 days)
     *               - 'months': Difference in months
     *               - 'bimesters': Difference in bimesters (1 bimester = 2 months)
     *               - 'quadrimesters': Difference in quadrimesters (1 quadrimester = 4 months)
     *               - 'semesters': Difference in semesters (1 semester = 6 months)
     *               - 'years': Difference in years
     *
     * @throws \Exception If either date is invalid, an exception will be thrown
     *                    with the message 'Invalid date format'.
     *
     * Example usage:
     * $startDate = '2023-01-01';
     * $endDate = '2024-01-01';
     * $difference = DateService::calculateDateDifference($startDate, $endDate);
     *
     * Expected response:
     * [
     *     'hours' => 8760,
     *     'days' => 365,
     *     'weeks' => 52,
     *     'fortnights' => 26,
     *     'months' => 12,
     *     'bimesters' => 6,
     *     'quadrimesters' => 3,
     *     'semesters' => 2,
     *     'years' => 1,
     * ]
     */
    public static function calculateDateDifference($startDate, $endDate)
    {
        // Parse the dates using the parseDate method
        $start = self::parseDate($startDate);
        $end = self::parseDate($endDate);

        if (!$start || !$end) {
            throw new \Exception('Invalid date format');
        }

        // Calculate differences in various units
        return [
            'hours' => $start->diffInHours($end),
            'days' => $start->diffInDays($end),
            'weeks' => $start->diffInWeeks($end),
            'fortnights' => floor($start->diffInDays($end) / 14), // 1 fortnight = 14 days
            'months' => $start->diffInMonths($end),
            'bimesters' => floor($start->diffInMonths($end) / 2), // 1 bimester = 2 months
            'quadrimesters' => floor($start->diffInMonths($end) / 4), // 1 quadrimester = 4 months
            'semesters' => floor($start->diffInMonths($end) / 6), // 1 semester = 6 months
            'years' => $start->diffInYears($end),
        ];
    }

    /**
     * Calculates a future date based on a start date and a time input.
     *
     * This method takes a start date and a time input string, which specifies
     * the amount of time to add (in various units) to the start date. The function
     * parses the start date and time input, and then adds the appropriate amount of
     * time based on the specified unit.
     *
     * The time input can be a string such as "5 days", "2 months", or "3 years".
     * If the start date or time input is invalid, an exception will be thrown.
     *
     * @param mixed $startDate The starting date, which can be a string
     *                         or a Carbon instance.
     * @param string $timeInput A string representing the amount of time to
     *                          add to the start date. It should be in the format
     *                          of "<number> <unit>", where <unit> can be
     *                          "hours", "days", "weeks", "fortnights",
     *                          "months", "bimesters", "quadrimesters",
     *                          "semesters", or "years".
     *
     * @return Carbon A Carbon instance representing the calculated future date
     *                after adding the specified time to the start date.
     *
     * @throws \Exception If the start date format is invalid, or if the time input
     *                    format is invalid, or if the time unit is unrecognized,
     *                    an exception will be thrown with an appropriate message.
     *
     * Example usage:
     * $startDate = '2023-01-01';
     * $timeInput = '2 months';
     * $futureDate = DateService::calculateFutureDate($startDate, $timeInput);
     *
     * Expected response (if $startDate is '2023-01-01' and $timeInput is '2 months'):
     * '2023-03-01 00:00:00' (or similar depending on time components of the original date)
     */
    public static function calculateFutureDate($startDate, int $timeInput)
    {
        // Parse the start date
        $start = self::parseDate($startDate);

        if (!$start) {
            throw new \Exception('Invalid start date format');
        }

        // Extract the value and unit from the input string
        if (preg_match('/(\d+)\s*(\w+)/', $timeInput, $matches)) {
            $value = intval($matches[1]); // The number
            $unit = strtolower($matches[2]); // The unit (e.g. "days", "months")
        } else {
            throw new \Exception('Invalid time input format');
        }

        // Determine the unit and add the appropriate amount of time
        switch ($unit) {
            case 'hours':
            case 'hour':
                return $start->addHours($value);
            case 'days':
            case 'day':
                return $start->addDays($value);
            case 'weeks':
            case 'week':
                return $start->addWeeks($value);
            case 'fortnights': // quinzenas
            case 'fortnight':
                return $start->addDays($value * 14);
            case 'months':
            case 'month':
                return $start->addMonths($value);
            case 'bimesters': // bimestres
            case 'bimester':
                return $start->addMonths($value * 2);
            case 'quadrimesters': // quadrimestres
            case 'quadrimester':
                return $start->addMonths($value * 4);
            case 'semesters':
            case 'semester':
                return $start->addMonths($value * 6);
            case 'years':
            case 'year':
                return $start->addYears($value);
            default:
                throw new \Exception('Invalid time unit');
        }
    }

    /**
     * Calculates a new date based on a start date and a time input.
     *
     * This method takes a start date, a time input string, and a boolean
     * flag indicating whether to calculate a future or past date. The function
     * parses the start date and time input, then either adds or subtracts the
     * specified amount of time from the start date based on the value of
     * the `$isFuture` parameter.
     *
     * The time input should be a string formatted as "<number> <unit>", where
     * <unit> can be "hours", "days", "weeks", "fortnights", "months",
     * "bimesters", "quadrimesters", "semesters", or "years".
     * If the start date or time input is invalid, an exception will be thrown.
     *
     * @param mixed $startDate The starting date, which can be a string
     *                         or a Carbon instance.
     * @param string $timeInput A string representing the amount of time to
     *                          add or subtract from the start date.
     *                          The format should be "<number> <unit>".
     * @param bool $isFuture A boolean indicating whether to calculate a
     *                       future date (true) or a past date (false).
     *
     * @return Carbon A Carbon instance representing the calculated date
     *                after applying the specified time adjustment to the start date.
     *
     * @throws \Exception If the start date format is invalid, if the time input
     *                    format is invalid, if the time unit is unrecognized,
     *                    or if the method fails to process, an exception
     *                    will be thrown with an appropriate message.
     *
     * Example usage:
     * $startDate = '2023-01-01';
     * $timeInput = '2 months';
     * $futureDate = DateService::calculateDate($startDate, $timeInput, true); // Future date
     * $pastDate = DateService::calculateDate($startDate, $timeInput, false);   // Past date
     *
     * Expected response (if $startDate is '2023-01-01' and $timeInput is '2 months'):
     * For future date: '2023-03-01 00:00:00' (or similar depending on time components)
     * For past date: '2022-11-30 00:00:00' (or similar depending on time components)
     */
    public static function calculateDate($startDate, $timeInput, $isFuture = true)
    {
        // Parse the start date
        $start = self::parseDate($startDate);

        if (!$start) {
            throw new \Exception('Invalid start date format');
        }

        // Extract the value and unit from the input string
        if (preg_match('/(\d+)\s*(\w+)/', $timeInput, $matches)) {
            $value = intval($matches[1]); // The number
            $unit = strtolower($matches[2]); // The unit (e.g. "days", "months")
        } else {
            throw new \Exception('Invalid time input format');
        }

        // Choose the correct method to subtract or add time based on $isFuture
        $method = $isFuture ? 'add' : 'sub';

        // Determine the unit and apply the appropriate method
        switch ($unit) {
            case 'hours':
            case 'hour':
                return $start->{$method . 'Hours'}($value);
            case 'days':
            case 'day':
                return $start->{$method . 'Days'}($value);
            case 'weeks':
            case 'week':
                return $start->{$method . 'Weeks'}($value);
            case 'fortnights': // quinzenas
            case 'fortnight':
                return $start->{$method . 'Days'}($value * 14);
            case 'months':
            case 'month':
                return $start->{$method . 'Months'}($value);
            case 'bimesters': // bimestres
            case 'bimester':
                return $start->{$method . 'Months'}($value * 2);
            case 'quadrimesters': // quadrimestres
            case 'quadrimester':
                return $start->{$method . 'Months'}($value * 4);
            case 'semesters':
            case 'semester':
                return $start->{$method . 'Months'}($value * 6);
            case 'years':
            case 'year':
                return $start->{$method . 'Years'}($value);
            default:
                throw new \Exception('Invalid time unit');
        }
    }


    /**
     * Generates date ranges based on a specified period.
     *
     * This function allows you to create date intervals such as weeks, biweeks, months,
     * bimonths, trimesters, semesters, and years within a date range defined by a start
     * date and an end date. Desired intervals can be specified through an array. If no
     * intervals are specified, all available intervals will be generated.
     *
     * @param Carbon $startDate The start date of the range.
     * @param Carbon $endDate The end date of the range.
     * @param array $desiredIntervals An optional array of strings representing the desired
     *                                intervals. Accepted values are:
     *                                'weekly', 'biweekly', 'monthly', 'bimonthly',
     *                                'quarterly', 'quadrimonthly', 'semiannually', and 'yearly'.
     *                                If not specified, all intervals will be used.
     *
     * @return array An associative array containing the generated date intervals. Each key
     *               in the array corresponds to a desired interval and contains an array of
     *               intervals with 'start' and 'end', representing the beginning and ending
     *               dates of each interval.
     *
     * Example usage:
     * $startDate = Carbon::parse('2023-01-01');
     * $endDate = Carbon::parse('2023-12-31');
     * $dateRanges = DateService::generateDateRanges($startDate, $endDate, ['weekly', 'monthly']);
     *
     * Expected response:
     * [
     *     'weekly' => [
     *         ['start' => '2023-01-02', 'end' => '2023-01-08'],
     *         ['start' => '2023-01-09', 'end' => '2023-01-15'],
     *         // ...continues
     *     ],
     *     'monthly' => [
     *         ['start' => '2023-01-01', 'end' => '2023-01-31'],
     *         ['start' => '2023-02-01', 'end' => '2023-02-28'],
     *         // ...continues
     *     ]
     * ]
     */
    public static function generateDateRanges(Carbon $startDate, Carbon $endDate, array $desiredIntervals = [])
    {
        $intervals = [];

        // Define todos os intervalos possíveis
        $allIntervals = [
            'weekly' => '1 week',
            'biweekly' => '2 weeks',
            'monthly' => '1 month',
            'bimonthly' => '2 months',
            'quarterly' => '3 months',
            'quadrimonthly' => '4 months',
            'semiannually' => '6 months',
            'yearly' => '1 year',
        ];

        // Se não houver intervalos desejados, use todos os intervalos
        if (empty($desiredIntervals)) {
            $desiredIntervals = array_keys($allIntervals);
        }

        // Helper function to generate date ranges based on a period
        function getRanges($startDate, $endDate, $period)
        {
            $ranges = [];
            foreach ($period as $date) {
                $start = $date->start;
                $end = $date->end->greaterThan($endDate) ? $endDate : $date->end;
                $ranges[] = ['start' => $start->toDateString(), 'end' => $end->toDateString()];
            }
            return $ranges;
        }

        // Itera sobre os intervalos desejados e gera as datas
        foreach ($desiredIntervals as $interval) {
            if (array_key_exists($interval, $allIntervals)) {
                switch ($interval) {
                    case 'weekly':
                        $weekPeriod = CarbonPeriod::create($startDate->copy()->startOfWeek(), $allIntervals[$interval], $endDate);
                        $intervals['weekly'] = getRanges(
                            $startDate,
                            $endDate,
                            $weekPeriod->map(function ($date) {
                                return (object)[
                                    'start' => $date->copy()->startOfWeek(),
                                    'end' => $date->copy()->endOfWeek(),
                                ];
                            })
                        );
                        break;

                    case 'biweekly':
                        $biWeekPeriod = CarbonPeriod::create($startDate->copy(), $allIntervals[$interval], $endDate);
                        $intervals['biweekly'] = getRanges(
                            $startDate,
                            $endDate,
                            $biWeekPeriod->map(function ($date) {
                                return (object)[
                                    'start' => $date->copy(),
                                    'end' => $date->copy()->addWeeks(2)->subDay(),
                                ];
                            })
                        );
                        break;

                    case 'monthly':
                        $monthPeriod = CarbonPeriod::create($startDate->copy()->startOfMonth(), $allIntervals[$interval], $endDate);
                        $intervals['monthly'] = getRanges(
                            $startDate,
                            $endDate,
                            $monthPeriod->map(function ($date) {
                                return (object)[
                                    'start' => $date->copy()->startOfMonth(),
                                    'end' => $date->copy()->endOfMonth(),
                                ];
                            })
                        );
                        break;

                    case 'bimonthly':
                        $biMonthPeriod = CarbonPeriod::create($startDate->copy(), $allIntervals[$interval], $endDate);
                        $intervals['bimonthly'] = getRanges(
                            $startDate,
                            $endDate,
                            $biMonthPeriod->map(function ($date) {
                                return (object)[
                                    'start' => $date->copy(),
                                    'end' => $date->copy()->addMonths(2)->subDay(),
                                ];
                            })
                        );
                        break;

                    case 'quarterly':
                        $quarterPeriod = CarbonPeriod::create($startDate->copy(), $allIntervals[$interval], $endDate);
                        $intervals['quarterly'] = getRanges(
                            $startDate,
                            $endDate,
                            $quarterPeriod->map(function ($date) {
                                return (object)[
                                    'start' => $date->copy(),
                                    'end' => $date->copy()->addMonths(3)->subDay(),
                                ];
                            })
                        );
                        break;

                    case 'quadrimonthly':
                        $quadMonthPeriod = CarbonPeriod::create($startDate->copy(), $allIntervals[$interval], $endDate);
                        $intervals['quadrimonthly'] = getRanges(
                            $startDate,
                            $endDate,
                            $quadMonthPeriod->map(function ($date) {
                                return (object)[
                                    'start' => $date->copy(),
                                    'end' => $date->copy()->addMonths(4)->subDay(),
                                ];
                            })
                        );
                        break;

                    case 'semiannually':
                        $semiAnnualPeriod = CarbonPeriod::create($startDate->copy(), $allIntervals[$interval], $endDate);
                        $intervals['semiannually'] = getRanges(
                            $startDate,
                            $endDate,
                            $semiAnnualPeriod->map(function ($date) {
                                return (object)[
                                    'start' => $date->copy(),
                                    'end' => $date->copy()->addMonths(6)->subDay(),
                                ];
                            })
                        );
                        break;

                    case 'yearly':
                        $yearPeriod = CarbonPeriod::create($startDate->copy()->startOfYear(), $allIntervals[$interval], $endDate);
                        $intervals['yearly'] = getRanges(
                            $startDate,
                            $endDate,
                            $yearPeriod->map(function ($date) {
                                return (object)[
                                    'start' => $date->copy()->startOfYear(),
                                    'end' => $date->copy()->endOfYear(),
                                ];
                            })
                        );
                        break;
                }
            }
        }

        return $intervals;
    }

    public function getZodiacSign($birthdate)
    {
        $date = self::parseDate($birthdate);

        if (!$date) {
            throw new \InvalidArgumentException("Formato de data inválido. Use o formato Y-m-d.");
        }

        $month = $date->format('m');
        $day = $date->format('d');

        if (($month == 1 && $day >= 20) || ($month == 2 && $day <= 18)) {
            return 'Aquário';
        } elseif (($month == 2 && $day >= 19) || ($month == 3 && $day <= 20)) {
            return 'Peixes';
        } elseif (($month == 3 && $day >= 21) || ($month == 4 && $day <= 19)) {
            return 'Áries';
        } elseif (($month == 4 && $day >= 20) || ($month == 5 && $day <= 20)) {
            return 'Touro';
        } elseif (($month == 5 && $day >= 21) || ($month == 6 && $day <= 20)) {
            return 'Gêmeos';
        } elseif (($month == 6 && $day >= 21) || ($month == 7 && $day <= 22)) {
            return 'Câncer';
        } elseif (($month == 7 && $day >= 23) || ($month == 8 && $day <= 22)) {
            return 'Leão';
        } elseif (($month == 8 && $day >= 23) || ($month == 9 && $day <= 22)) {
            return 'Virgem';
        } elseif (($month == 9 && $day >= 23) || ($month == 10 && $day <= 22)) {
            return 'Libra';
        } elseif (($month == 10 && $day >= 23) || ($month == 11 && $day <= 21)) {
            return 'Escorpião';
        } elseif (($month == 11 && $day >= 22) || ($month == 12 && $day <= 21)) {
            return 'Sagitário';
        } elseif (($month == 12 && $day >= 22) || ($month == 1 && $day <= 19)) {
            return 'Capricórnio';
        } else {
            throw new \InvalidArgumentException("Data de nascimento inválida.");
        }
    }
}
