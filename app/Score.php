<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Score extends Model
{
    protected $fillable = ['id', 'score_name', 'website_id'];

    /**
     * Increments the score values
     *
     * @param $score_name the score identifier name
     * @param $website_id the website unique id
     * @param $increment_by the website unique id
     *
     * @return false if an error occured
     */
    public static function up($score_name, $website_id, $increment_by = 1) {
        // bail early if one of the parameters is empty
        // that's very useful because $increment_by might be set to 0 sometimes
        if (empty($score_name) || empty($website_id) || empty($increment_by)) {
            return false;
        }

        $score = self::firstOrCreate(
            // querying for these fields
            [
                'score_name' => $score_name,
                'website_id' => $website_id
            ],
            // create with these fields
            [
                'id' => Uuid::uuid4()->toString()
            ]
        );

        // increment both values
        $score->score_value_since_day1 += $increment_by;
        $score->score_value += $increment_by;

        $score->save();
    }
}
