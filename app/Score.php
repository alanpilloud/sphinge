<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    // allow firstOrCreate to fill these fields
    protected $fillable = ['score_name', 'website_id'];

    // dont use timestamps
    public $timestamps = false;

    /**
     * Increments the score values
     *
     * @param $score_name the score identifier name
     * @param $website_id the website unique id
     * @param $increment_by allows to set the incrementation of the score
     *
     * @return void or false if an error occured
     */
    public static function up($score_name, $website_id, $increment_by = 1) {
        // bail early if one of the parameters is empty
        // that's very useful because $increment_by might be set to 0 sometimes
        if (empty($score_name) || empty($website_id) || empty($increment_by)) {
            return false;
        }

        $score = self::firstOrNew(
            // querying for these fields
            [
                'score_name' => $score_name,
                'website_id' => $website_id
            ]
        );

        // increment both values
        $score->score_value_since_day1 += $increment_by;
        $score->score_value += $increment_by;

        $score->save();
    }
}
