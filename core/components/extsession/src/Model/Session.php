<?php

namespace ExtSession\Model;

use MODX\Revolution\modX;
use MODX\Revolution\modSession;

/**
 * Class Session
 *
 *
 * @package ExtSession\Model
 */
class Session extends modSession
{
    /**
     * @param         $n
     * @param array $p
     */
    public function __call($n, array $p)
    {
        echo __METHOD__ . " says: " . $n;
    }

    /**
     * @param null $cacheFlag
     *
     * @return bool
     */
    public function save($cacheFlag = null)
    {
        $isProcess = (parent::isNew() || parent::isDirty('access'));

        if ($isProcess) {
            $userId = 0;
            if ($this->xpdo instanceof modX) {
                $contextKey = isset($this->xpdo->context) ? $this->xpdo->context->key : '';
                if ($contextKey && isset($_SESSION['modx.user.contextTokens']) && isset($_SESSION['modx.user.contextTokens'][$contextKey])) {
                    $userId = (int)$_SESSION['modx.user.contextTokens'][$contextKey];
                }
            }

            parent::set('user_id', $userId);
            parent::set('user_ip', self::_getClientIpAddress());

            $userAgent = self::_getClientUserAgent();
            parent::set('user_agent', $userAgent);
            $userBot = false;
            $botPattern = trim($this->xpdo->getOption('extsession_bot_patterns'));
            if ($botPattern) {
                $userBot = self::_validateClientIsBot($userAgent, $botPattern);
            }
            parent::set('user_bot', $userBot);
        }

        $saved = parent::save($cacheFlag);

        return $saved;
    }

    /**
     * Ensures an IP address is both a valid IP address and does not fall within
     * a private network range.
     *
     * @param $ip
     *
     * @return bool
     */
    public static function _validateClientIp($ip)
    {
        if (strtolower($ip) === 'unknown')
            return false;

        // Generate IPv4 network address
        $ip = ip2long($ip);

        // If the IP address is set and not equivalent to 255.255.255.255
        if ($ip !== false && $ip !== -1) {
            // Make sure to get unsigned long representation of IP address
            // due to discrepancies between 32 and 64 bit OSes and
            // signed numbers (ints default to signed in PHP)
            $ip = sprintf('%u', $ip);

            // Do private network range checking
            if ($ip >= 0 && $ip <= 50331647)
                return false;
            if ($ip >= 167772160 && $ip <= 184549375)
                return false;
            if ($ip >= 2130706432 && $ip <= 2147483647)
                return false;
            if ($ip >= 2851995648 && $ip <= 2852061183)
                return false;
            if ($ip >= 2886729728 && $ip <= 2887778303)
                return false;
            if ($ip >= 3221225984 && $ip <= 3221226239)
                return false;
            if ($ip >= 3232235520 && $ip <= 3232301055)
                return false;
            if ($ip >= 4294967040)
                return false;
        }

        return true;
    }

    /**
     * @return mixed|string
     */
    public static function _getClientIpAddress()
    {
        // Check for shared Internet/ISP IP
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && self::_validateClientIp($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        // Check for IP addresses passing through proxies
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

            // Check if multiple IP addresses exist in var
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
                $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($iplist as $ip) {
                    if (self::_validateClientIp($ip))
                        return $ip;
                }
            } else {
                if (self::_validateClientIp($_SERVER['HTTP_X_FORWARDED_FOR']))
                    return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED']) && self::_validateClientIp($_SERVER['HTTP_X_FORWARDED']))
            return $_SERVER['HTTP_X_FORWARDED'];
        if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && self::_validateClientIp($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
            return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && self::_validateClientIp($_SERVER['HTTP_FORWARDED_FOR']))
            return $_SERVER['HTTP_FORWARDED_FOR'];
        if (!empty($_SERVER['HTTP_FORWARDED']) && self::_validateClientIp($_SERVER['HTTP_FORWARDED']))
            return $_SERVER['HTTP_FORWARDED'];
        if (!empty($_SERVER['REMOTE_ADDR']) && self::_validateClientIp($_SERVER['REMOTE_ADDR']))
            return $_SERVER['REMOTE_ADDR'];

        return 'unknown';
    }

    /**
     * @return string
     */
    public static function _getClientUserAgent()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $v = self::_sanitizeString($_SERVER['HTTP_USER_AGENT']);
            return trim(mb_substr($v, 0, 100, 'utf-8'));
        }
        return 'unknown';
    }

    /**
     * @param string $userAgent
     * @param string $pattern
     *
     * @return bool
     */
    public static function _validateClientIsBot($userAgent = '', $pattern = 'Bot|Crawler')
    {
        $pattern = explode('|', $pattern);
        $pattern = array_map('trim', $pattern);       // Trim array's values
        $pattern = array_keys(array_flip($pattern));  // Remove duplicate fields
        $pattern = array_filter($pattern);            // Remove empty values from array
        if (empty($pattern)) {
            return false;
        }
        $pattern = '~(' . implode('|', $pattern) . ')~i';
        if (preg_match($pattern, $userAgent)) {
            return true;
        }
        return false;
    }

    /**
     * Sanitizes a string
     *
     * @param string $str The string to sanitize
     * @param array $chars An array of chars to remove
     * @param string $allowedTags A list of tags to allow.
     *
     * @return string The sanitized string.
     */
    public static function _sanitizeString($str, $chars = ['/', "'", '"', '(', ')', ';', '>', '<'], $allowedTags = '')
    {
        $str = str_replace($chars, '', strip_tags($str, $allowedTags));
        return preg_replace("/[^A-Za-z0-9_\-\.\/\\p{L}[\p{L} _.-]/u", '', $str);
    }


}
