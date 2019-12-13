<?php

use Illuminate\Support\HtmlString;

if (!function_exists('str_match')) {
    function str_match($pattern, $value)
    {
        if ($pattern == $value) {
            return true;
        }
        $pattern = preg_quote($pattern, '#');
        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $pattern = str_replace('\*', '.*', $pattern) . '\z';
        return (bool)preg_match('#^' . $pattern . '#', $value);
    }
}

if (!function_exists("generate_no")) {
    function generate_no($pattern, Closure $checkIfExists = null, $seed = null)
    {
        return Sequence\Factory::create()->next($pattern, $checkIfExists, $seed);
    }
}

if (!function_exists("create_transaction")) {
    function create_transaction($details)
    {
        if ($details['amount'] != 0) {
            return \App\Transaction::query()->create($details);
        }
    }
}

if (!function_exists("array_homogeneous")) {
    function array_homogeneous($array)
    {
        $first = current($array);

        foreach ($array as $value) {
            if ($first !== $value) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('parse_boolean')) {
    /**
     * @param $value
     * @return boolean
     */
    function parse_boolean($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}

if (!function_exists('mpesa')) {
    /**
     * @return \App\Foundation\Mpesa\Mpesa
     */
    function mpesa()
    {
        return app(\App\Foundation\Mpesa\Mpesa::class);
    }
}

if (!function_exists('user')) {
    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    function user()
    {
        $guards = array_keys(config('auth.guards'));
        foreach ($guards as $guard) {
            if (auth($guard)->check()) {
                return auth($guard)->user();
            }
        }
        return null;
    }
}

if (!function_exists('date_range')) {
    /**
     * @return string
     * @internal param $str
     */
    function date_range($from, $to = null, $long = true)
    {
        if (!empty($from) && !empty($to) && $from != '0000-00-00 00:00:00' && $to != '0000-00-00 00:00:00') {
            $from = strtotime($from);
            $to = strtotime($to);

            if ($from <= $to) {
                $start = $from;
                $end = $to;
            } else {
                $start = $to;
                $end = $from;
            }

            $start_year = ($long ? date('Y', $start) : date('y', $start));
            $end_year = ($long ? date('Y', $end) : date('y', $end));

            $start_month = (int)date('m', $start);
            $end_month = (int)date('m', $end);

            $start_date = (int)date('j', $start);
            $end_date = (int)date('j', $end);


            if ($start_year == $end_year) {
                if ($start_month == $end_month) {
                    if ($start_date == $end_date) {
                        $result = $start_date . ' ' . ($long ? date('F', $start) : date('M', $start));
                    } else {
                        $result = $start_date . '-' . $end_date . ' ' . ($long ? date('F', $start) : date('M', $start));
                    }
                } else {
                    $result = $start_date . ' ' . ($long ? date('F', $start) : date('M', $start)) . ' - ' . $end_date . ' ' . ($long ? date('F', $end) : date('M', $end));
                }

                $result .= ' ' . $start_year;
            } else {
                $result = $start_date . ' ' . ($long ? date('F', $start) : date('M', $start)) . ' ' . $start_year . ' - ' . $end_date . ' ' . ($long ? date('F', $end) : date('M', $end)) . ' ' . $end_year;
            }
        } elseif (!empty($from) && $from != '0000-00-00 00:00:00') {
            $timestamp = strtotime($from);
            $date = (int)date('j', $timestamp);
            $month = (string)($long ? date('F', $timestamp) : date('M', $timestamp));
            $year = ($long ? date('Y', $timestamp) : date('y', $timestamp));
            $result = $date . ' ' . $month . ' ' . $year;
        } elseif (!empty($to) && $to != '0000-00-00 00:00:00') {
            $timestamp = strtotime($to);
            $date = (int)date('j', $timestamp);
            $month = (string)($long ? date('F', $timestamp) : date('M', $timestamp));
            $year = ($long ? date('Y', $timestamp) : date('y', $timestamp));
            $result = $date . ' ' . $month . ' ' . $year;
        } else {
            $result = '';
        }
        return $result;
    }
}

if (!function_exists('normalize')) {
    function normalize($value)
    {
        for ($x = strlen($value) - 1; $x > 1; $x--) {
            if ($value[$x] == '0') {
                $value[$x] = ' ';
            } else {
                break;
            }
        }
        $value = trim($value);
        if ($value[strlen($value) - 1] == '.') {
            $value[strlen($value) - 1] = ' ';
        }
        return trim($value);
    }
}
if (!function_exists('currency')) {

    /**
     * @param int $amount
     * @return string
     */
    function currency($amount, $signed = true, $precision = 2, $short = false)
    {
        if (!is_numeric($amount)) {
            $amount = 0.0;
        }

        if (!$signed) {
            $amount = abs($amount);
        }

        if (!$short) {
            return number_format($amount, $precision, '.', ',');
        } else {
            $divisors = [
                pow(1000, 0) => '', // 1000^0 == 1
                pow(1000, 1) => 'K', // Thousand
                pow(1000, 2) => 'M', // Million
                pow(1000, 3) => 'B', // Billion
                pow(1000, 4) => 'T', // Trillion
            ];

            // Loop through each $divisor and find the
            // lowest amount that matches
            $divisor = pow(1000, 0);
            foreach ($divisors as $divisor => $shorthand) {
                if (abs($amount) < ($divisor * 1000)) {
                    // We found a match!
                    break;
                }
            }

            // We found our match, or there were no matches.
            // Either way, use the last defined value for $divisor.
            return number_format($amount / $divisor, $precision) . $shorthand;
        }
    }
}

if (!function_exists('coalesce')) {
    function coalesce()
    {
        $data = func_get_args();
        foreach ($data as $arg) {
            if ($arg) {
                return $arg;
            }
        }
    }
}
if (!function_exists('route_matches')) {
    function route_matches($pattern, $url = null)
    {
        if ($url === null) {
            $url = request()->getPathInfo();
        }
        if ($url[0] == '/') {
            $url = substr($url, 1);
        }
        $route = request()->route();
        $action = optional($route->action);
        $name = $action['as'];
        $uri = $route->uri;
        if (str_match($pattern, $uri) || str_match($pattern, $url) || str_match($pattern, $name)) {
            return true;
        }
        return false;
    }
}


if (!function_exists('date_picker')) {
    /**
     * @param string $label
     * @param string $name
     * @param DateTime $default
     * @param int $label_size
     * @param int $input_size
     * @return HtmlString
     */
    function date_picker($label = 'Date Picker', $name = '', $default = null)
    {
        if (empty($name) && !empty($label)) {
            $name = str_replace(' ', '_', $label);
        }
        if (!$default) {
            $default = date('Y-m-d');
        }
        $html = '<div class="form-group"><label class="control-label">' . ucwords($label) . '</label><div><div class="input-group date" data-provide="datepicker" data-date-format="yyyy-mm-dd"><span class="input-group-addon"><i class="fa fa-calendar"></i></span><input type="text" name="' . $name . '" class="form-control" value="' . $default . '" placeholder="yyyy-mm-dd"></div></div></div>';
        return new HtmlString($html);
    }
}
