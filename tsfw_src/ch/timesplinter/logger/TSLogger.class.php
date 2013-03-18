<?php
/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013, Pascal Muenst
 * @version 1.0.0
 */

namespace ch\timesplinter\logger;


use ch\timesplinter\core\Settings;

class TSLogger {
    private static $settings;

    public static function getLoggerByName($name, $classContext) {
        if(self::$settings === null) {
            self::$settings = new Settings(SETTINGS_DIR, array(
                'fw_dir' => FW_DIR,
                'site_root' => SITE_ROOT
            ));
        }

        if(!isset(self::$settings->loggers->$name)) {
            throw new \Exception('Logger with name "' . $name . '" is not defined in ' . SETTINGS_DIR . 'loggers.json');
        }

        $loggerSettings = self::$settings->loggers->$name;
        $loggerInstance = null;

        if($loggerSettings->type === 'Logger::TYPE_FILE') {
            $loggerInstance = new FileLogger(
                $classContext,
                $loggerSettings->log_level,
                $loggerSettings->logfilepath,
                $loggerSettings->maxfilesize
            );

            if(isset($loggerSettings->pattern))
                $loggerInstance->setPattern($loggerSettings->pattern);
        } elseif($loggerSettings->type === 'Logger::TYPE_DB') {
            // @TODO implement the db logger
        } elseif($loggerSettings->type === 'Logger::TYPE_STDOUT') {
            $loggerInstance = new StdOutLogger($classContext, $loggerSettings->log_level);

            if(isset($loggerSettings->pattern))
                $loggerInstance->setPattern($loggerSettings->pattern);
        } else {
            throw new \Exception('Uknown logger type: ' . $loggerSettings->type . ' for logger with name "' . $name . '"');
        }

        return $loggerInstance;
    }
}

/* EOF */