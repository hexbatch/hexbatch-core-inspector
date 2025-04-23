<?php

namespace App\Helpers;


use App\Models\User;
use Carbon\Carbon;

use Illuminate\Support\Facades\Auth;


class Utilities {

    static function markUnusedVar($arg, ...$arr) {
        //nothing done here, function prevents some linting warning notices for unused
    }

    /**
     * @author https://stackoverflow.com/a/23888858
     */
    static function human_filesize(int $bytes, int $dec = 2): string {

        $size   = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        if ($factor == 0) $dec = 0;
        $amount = $bytes / (1024 ** $factor);
        if (round($amount) === $amount) {$dec = 0;}

        return sprintf("%.{$dec}f %s", $bytes / (1024 ** $factor), $size[$factor]);

    }



    public static function formatHoursMinutes(?float $what) : string  {
        if (!$what) {return '';}
        $what = abs($what);
        if ($what <= 0) {
            return '';
        }
        $hours = floor($what) ;
        $minutes = ($what - $hours) * 60;
        if ( !$hours && $minutes < 0.5) {
            return "< 1m";
        }
        return sprintf('%sh %sm', $hours, round($minutes) );
    }

    public static function to_int_array($given, bool $b_allow_negative = true, bool $b_allow_zero = true) : array {
        if (empty($given)) {return [];}
        $positive_int_only_array = [];
        foreach ($given as $id_raw ) {
            $da_id = (int)$id_raw;
            if (!$b_allow_negative) {
                if ($da_id < 0) {continue;}
            }
            if (!$b_allow_zero) {
                if ($da_id === 0) {continue;}
            }

            $positive_int_only_array[] = $da_id;
        }
        return $positive_int_only_array;
    }

    public static function get_logged_user(bool $b_throw_on_unlogged = true) : ?User {
        /**
         * @var User|null $user
         */
        $user = Auth::user();


        if (empty($user) || empty($user->id)) {
            if ($b_throw_on_unlogged) {
                throw new \RuntimeException("User is not logged in");
            }
        }
        return $user;
    }


    public static function getComposerPath() : string {
        $composerFile = base_path() . DIRECTORY_SEPARATOR . 'composer.json';
        $what =  realpath($composerFile);
        if (!$what) {
            throw new \LogicException("Composer path $composerFile does not exist");
        }
        return $what;
    }

    public static function getComposer() : array  {
        $composerFile = static::getComposerPath();
        $composer = json_decode(file_get_contents($composerFile), true);
        if (empty($composer)) {
            throw new \LogicException("Cannot convert composer.json");
        }
        return $composer;
    }

    public static function getVersionAsString() : string {
        $composer = static::getComposer();
        return $composer['version']??'';
    }

    public static function getInstallTimeStamp() : ?int {
        $what =  filemtime(self::getComposerPath());
        if (!$what) {return null;}
        return $what;
    }


    public static function parseTimeAsUtc(null|int|string|Carbon $what) : ?Carbon {
      if (!$what) {return null;}
      if ($what instanceof Carbon) {return $what->timezone('UTC');}
      if (is_int($what) || ctype_digit($what)) {
          return Carbon::createFromTimestamp($what)->timezone('UTC');
      }
      return Carbon::parse($what)->timezone('UTC');
    }


}
