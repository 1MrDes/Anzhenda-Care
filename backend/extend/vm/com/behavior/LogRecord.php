<?php


namespace vm\com\behavior;


use vm\com\SeasLog;

class LogRecord
{
    public function handle(\think\event\LogRecord $log)
    {
        if ((PHP_SAPI != 'cli') || (PHP_SAPI == 'cli' && in_array(strtolower($log->type), ['error', 'warning', 'emergency', 'alert', 'critical']))) {
            SeasLog::$log[] = '[' . $log->type . '] ' . (is_string($log->message) ? $log->message : var_export($log->message, true));
        }
    }
}